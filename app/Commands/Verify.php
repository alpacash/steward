<?php

namespace App\Commands;

class Verify extends StackCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'stack:status';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Verify the stack status';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function process(): int
    {
        return $this->status();
    }
}
