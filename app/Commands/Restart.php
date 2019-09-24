<?php

namespace App\Commands;

class Restart extends StackCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'stack:restart {server?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Restart the stack';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function process(): int
    {
        $choice = $this->argument('server');

        foreach ($this->stack->servers() as $server) {
            if (empty($choice) || $server->label() === $choice) {
                $this->output->note("Restarting {$server->label()}");
                $server->restart();
            }
        }

        if (empty($choice)) {
            return $this->success("Done!");
        }

        return $this->fail($choice . " does not seem like a valid server.");
    }
}
