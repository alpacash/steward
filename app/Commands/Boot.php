<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Boot extends StackCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'boot';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Boot the servers';

    /**
     * @return int
     */
    public function process(): int
    {
        foreach ($this->stack->servers() as $server) {
            $this->output->note("Restarting server {$server->label()}");

            $server->config()->save();
            $server->restart();
        }

        return $this->status();
    }
}
