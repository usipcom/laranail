<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Laravel\Console\Events;

use Closure;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;

/**
 * https://gist.github.com/ctf0/142e1433c10da2e726cd9b4fe49ac3a7
 * https://ctf0.wordpress.com/2018/01/16/listen-to-console-commands-events-in-laravel/
 */
class CommandEventsListener
{

    protected array $startCallbacks  = [];

    protected array $finishCallbacks = [];

    public function __construct($commandName)
    {
        app('events')->listen(CommandStarting::class, function ($event) use ($commandName) {
            if ($event->command == $commandName) {
                $this->callStartCallbacks();
            }
        });

        app('events')->listen(CommandFinished::class, function ($event) use ($commandName) {
            if ($event->command == $commandName) {
                $this->callFinishCallbacks();
            }
        });
    }

    public function onStart(Closure $callback): static
    {
        $this->startCallbacks[] = $callback;

        return $this;
    }

    public function onFinish(Closure $callback): static
    {
        $this->finishCallbacks[] = $callback;

        return $this;
    }

    protected function callStartCallbacks(): void
    {
        foreach ($this->startCallbacks as $callback) {
            $this->exec($callback);
        }
    }

    protected function callFinishCallbacks(): void
    {
        foreach ($this->finishCallbacks as $callback) {
            $this->exec($callback);
        }
    }

    protected function exec($callBack): mixed
    {
        return call_user_func($callBack);
    }

}