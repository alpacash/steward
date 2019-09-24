<?php

namespace App\Commands;

class Verify extends StackCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'stack:verify';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Verify the stack';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function process(): int
    {
        return $this->success(trim(shell_exec("stew -V"))
            . " // [Php]: " . $this->stack->phpServer()->version()
            . " // [Caddy]: " . $this->stack->httpServer()->version()
            . " // [Dnsmasq]: " . $this->stack->dnsServer()->version());
    }
}
