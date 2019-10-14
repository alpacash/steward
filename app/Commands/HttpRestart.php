<?php

namespace App\Commands;

class HttpRestart extends StackCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:restart';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Restart http server';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function process(): int
    {
        $this->output->note("Writing caddy http configuration files...");
        $this->stack->httpServer()->config()->save();

        $this->output->note("Restarting caddy http server...");
        $this->stack->httpServer()->restart();

        $this->output->success("Done!");

        return $this->success();
    }
}
