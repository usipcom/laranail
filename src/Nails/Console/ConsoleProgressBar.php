<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Console;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleProgressBar
{

    public ?ConsoleOutput     $consoleOutput;
    public ?ProgressBar       $progressBar;
    public SymfonyStyle       $style;
    protected string|null     $taskLabel = "";
    protected string          $spaceFormatting  = "  ";

    public function __construct()
    {
        $this->reset();
        $this->consoleOutput = new ConsoleOutput();
    }

    public function setTaskLabel(?string $taskLabel): static
    {
        $this->taskLabel = $taskLabel;

        return $this;
    }

    public function getTaskLabel(): ?string
    {
        return $this->taskLabel;
    }

    private function reset(): void
    {
        $this->progressBar = null;
        $this->taskLabel   = null;
    }

    /**
     * Count the total number of items in a given array or object
     *
     * @param array|object|int $data
     *
     * @return object|int
     */
    private function count(array|object|int $data): object|int
    {
        return (is_array($data) || is_object($data)) ? pheg()->arr()->count(pheg()->transfigure()->toArray($data)) : $data;
    }

    public function iterate(object|array $data): iterable
    {
        return $this->progressBar->iterate($data);
    }

    private function generateProgressBar(array|object|int $data): static
    {

        // create a progress bar instance if it doesn't exist yet
        $this->progressBar = new ProgressBar($this->consoleOutput,  $this->count($data));

        // define the placeholder format
        ProgressBar::setPlaceholderFormatterDefinition('memory', function (ProgressBar $bar) {
            static $i = 0;
            $memory   = 100000 * $i;
            $colors   = $i++ ? '41;37' : '44;37';
            return "\033[" . $colors . 'm' . Helper::formatMemory($memory) . "\033[0m";
        });

        // set formats
        $this->progressBar->setFormat('debug');
        $this->progressBar->setFormat("\r{$this->spaceFormatting}<fg=gray>%current%/%max% [%bar%] | %percent%% %remaining% | %elapsed:6s% / %estimated:-6s% | </>%memory:6s%");

        // redraw
        $this->progressBar->setRedrawFrequency(100);
        $this->progressBar->maxSecondsBetweenRedraws(0.2);
        $this->progressBar->minSecondsBetweenRedraws(0.1);

        // starts and displays the progress bar
        $this->progressBar->start();

        return $this;
    }

    public function startProgressBar(string|null $label, object|array|int $data): static
    {
        return $this->setTaskLabel($label)->generateProgressBar($data);
    }

    public function advanceProgressBar(int $step = 1, int $sleep = 1500): static
    {
        if ($step < 1) {
            $step = 1;
        }

        $this->progressBar->advance($step);
        usleep($sleep);

        return $this;
    }

    public function finishProgressBar(string $message = "", bool $newline = true): void
    {

        // set task label
        $label = ucfirst(strtolower($this->taskLabel));

        $this->progressBar->finish();

        $message = !empty($message) ? "| {$message}" : "";

        $this->consoleOutput->write("<fg=gray> | $label {$message}</> \r");

        if ($newline) {
            $this->consoleOutput->write("", true);
        }

        $this->reset();
    }

    /**
     * @return ConsoleOutput|null
     */
    public function getConsoleOutput(): ?ConsoleOutput
    {
        return $this->consoleOutput;
    }

}
