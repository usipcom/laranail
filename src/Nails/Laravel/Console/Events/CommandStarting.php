<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Laravel\Console\Events;

use Symfony\Component\Console\Input\InputInterface;

class CommandStarting
{
    /**
     * The command that is starting.
     * @var object
     */
    public $command;

    /**
     * The console input.
     *
     * @var string
     */
    public $input;
   
    /**
     * Create a new event instance.
     * @param object         $command The command that is starting.
     * @param  InputInterface $input   Input Interface.
     */
    public function __construct(object $command, InputInterface $input)
    {
        $this->command = $command;
        $this->input   = $input;
    }
}
