<?php

namespace App\Commands;

use App\Shell;
use LaravelZero\Framework\Commands\Command;

class RedisTail extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'redis:tail';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Tail redis activity';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->note("Tailing activity @ redis-cli");

        Shell::cmd("redis-cli monitor", $this->output, null);

        return 1;
    }
}
