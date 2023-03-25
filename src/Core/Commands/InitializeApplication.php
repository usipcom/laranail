<?php declare(strict_types=1);

namespace Simtabi\Laranail\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class InitializeApplication extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application namespace';

    /**
     * The Composer class instance.
     *
     * @var Composer
     */
    protected Composer $composer;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * Current root application namespace.
     *
     * @var string
     */
    protected string $currentRoot;

    /**
     * Create a new key generator command.
     *
     * @param Composer $composer
     * @param Filesystem $files
     * @return void
     */
    public function __construct(Composer $composer, Filesystem $files)
    {
        parent::__construct();

        $this->composer = $composer;
        $this->files    = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Application namespace set!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The desired namespace'],
        ];
    }


    protected function generateEnv(): static
    {
        $this->components->info('Generate .env file');
        if ($this->confirm('Do you wish to continue?')) {
            // ...         // cp .env.example .env
        }

        return $this;
    }


    protected function generateApplicationKey(): static
    {
        $this->components->info('Generate application key');
        if ($this->confirm('Do you wish to continue?')) {
            $this->call('artisan key:generate');
        }

        return $this;
    }
}