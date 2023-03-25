<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Seeder\Traits;

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Simtabi\Laranail\Core\Facades\LaranailFacade;
use Simtabi\Laranail\Nails\Laravel\Console\Events\CommandEventsListener;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

trait HasDatabaseSeeder 
{

    public function getOutput(): ConsoleOutput
    {
        return new ConsoleOutput();
    }
    
    protected function initializedSeeder(?string $src = null, string $targetCommand = 'cms:install'): static
    {
        $this->loadSeeders($this->loadSeedersFrom($src), [], $targetCommand, false);

        return $this;
    }

    protected function loadSeedersFrom(?string $path): ?string
    {
        if (empty($path)) {
            if (method_exists($this, 'getPackageSeedersPath')) {
                return $this->getPackageSeedersPath();
            }
        }

        return $path;
    }

    protected function loadSeeders(?string $src = null, array $commands = [], ?string $targetCommand = null, null|bool $started = true): static
    {
        $this->germinate($this->loadSeedersFrom($src), $commands, $targetCommand, $started);

        return  $this;
    }

    /**
     * Seeds package files
     *
     * @param string|null $src
     * @param array       $commands
     * @param string|null $targetCommand
     * @param bool|null   $started
     *
     * @return void
     */
    public function germinate(?string $src, array $commands = [], ?string $targetCommand = null, null|bool $started = true): void
    {

        if ($this->app->runningInConsole() && !empty($src))
        {

            if (!empty($targetCommand)) {
                if ($started) {
                    $this->germinateSeedsAfterSpecificConsoleCommandHasStarted($src, $targetCommand);
                } else {
                    $this->germinateSeedsAfterSpecificConsoleCommandIsFinished($src, $targetCommand);
                }
            } else {
                $default  = [ 'db:seed', '--seed' ];
                $commands = !empty($commands) ? array_merge($default, $commands) : $default;

                if ($this->isConsoleCommandContains($commands, [ '--class', 'help', '-h' ])) {
                    $this->germinateSeedsAfterCurrentCommandsHaveExecuted($src);
                }
            }

        }
    }

    /**
     * Get a value that indicates whether the current command in console
     * contains a string in the specified $fields.
     *
     * @param array|string $includeOptions
     * @param array|string|null $excludeOptions
     *
     * @return bool
     */
    protected function isConsoleCommandContains(array|string $includeOptions, array|string|null $excludeOptions = null) : bool
    {
        $args = Request::server('argv', null);
        if (is_array($args)) {
            $command = implode(' ', $args);
            if (Str::contains($command, $includeOptions) && ($excludeOptions == null || !Str::contains($command, $excludeOptions)))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Germinate after the current console commands has executed successfully
     */
    protected function germinateSeedsAfterCurrentCommandsHaveExecuted(?string $src): void
    {
        Event::listen(CommandFinished::class, function(CommandFinished $event) use ($src) {
            // Accept command in console only, and exclude all commands from Artisan::call() method.
            if ($event->output instanceof ConsoleOutput) {
                $this->loadSeedsFromPath($src);
            }
        });
    }

    /**
     * Germinate seeds after a specific console command has started executing
     */
    protected function germinateSeedsAfterSpecificConsoleCommandHasStarted(?string $src, string $targetCommand): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event) use ($src, $targetCommand) {
            if ($event->command == $targetCommand) {
                $this->loadSeedsFromPath($src);
            }
        });
    }

    /**
     * Germinate seeds after a specific artisan command has started executing
     */
    protected function germinateSeedsAfterSpecificArtisanCommandHasStarted(?string $src, string $targetCommand): void
    {
        Event::listen(ArtisanStarting::class, function (ArtisanStarting $event) use ($src, $targetCommand) {

            if ($event->artisan == $targetCommand) {
                $this->loadSeedsFromPath($src);
            }
        });
    }

    /**
     * Germinate seeds after a specific console command has finished executing
     */
    protected function germinateSeedsAfterSpecificConsoleCommandIsFinished(?string $src, string $targetCommand): void
    {
        Event::listen(CommandFinished::class, function (CommandFinished $event) use ($src, $targetCommand) {
            if ($event->command == $targetCommand) {
                $this->loadSeedsFromPath($src);
            }
        });
    }

    public function getOutputFormatterStyle(string $foreground = null, string $background = null, array $options = []): OutputFormatterStyle
    {
        return new OutputFormatterStyle($foreground, $background, $options);
    }

    public function getOutputFormatter(string $name, string $foreground = null, string $background = null, array $options = []): OutputFormatterStyle
    {
        return $this->getOutput()->getFormatter()->setStyle($name, $this->getOutputFormatterStyle($foreground, $background, $options));
    }

    /**
     * Register seeds.
     *
     * @param string $path
     * @return void
     */
    protected function loadSeedsFromPath(string $path): void
    {

        LaranailFacade::writeToConsoleOutput(Info::class, $this->getOutput(),'Running migrations.');

        foreach (glob( $path . '/*.php') as $filename)
        {
            $classes = $this->getClassesFromFile($filename);
            foreach ($classes as $class) {
                with(new TwoColumnDetail($this->getOutput()))->render(
                    $class,
                    '<fg=yellow;options=bold>RUNNING</>'
                );

                $startTime = microtime(true);
                Artisan::call('db:seed', [ '--class' => $class, '--force' => true ]);
                $runTime   = number_format((microtime(true) - $startTime) * 1000, 2);

                with(new TwoColumnDetail($this->getOutput()))->render(
                    $class,
                    "<fg=gray>$runTime ms</> <fg=green;options=bold>DONE</>"
                );

                $this->getOutput()->writeln('');

            }
        }

    }

    /**
     * Get full class names declared in the specified file.
     *
     * @param string $filename
     * @return array an array of class names.
     */
    private function getClassesFromFile(string $filename) : array
    {
        // Get namespace of class (if vary)
        $namespace      = "";
        $lines          = file($filename);
        $namespaceLines = preg_grep('/^namespace /', $lines);

        if (is_array($namespaceLines)) {
            $namespaceLine = array_shift($namespaceLines);
            $match         = [];
            preg_match('/^namespace (.*);$/', $namespaceLine, $match);
            $namespace     = array_pop($match);
        }

        // Get name of all class has in the file.
        $classes  = [];
        $phpCode  = file_get_contents($filename);
        $tokens   = token_get_all($phpCode);
        $count    = count($tokens);

        for ($i = 2; $i < $count; $i++)
        {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
            {
                $className = $tokens[$i][1];
                if ($namespace !== "") {
                    $classes[] = $namespace . "\\$className";
                } else {
                    $classes[] = $className;
                }
            }
        }

        return $classes;
    }

}
