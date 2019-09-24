<?php

namespace App\Commands;

use App\Shell;
use App\StewardConfig;
use LaravelZero\Framework\Commands\Command;

class LogsTail extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logs:tail';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logsHome = StewardConfig::logsHome();

        $this->output->note("Tailing logs @ {$logsHome}");

        Shell::cmd("tail -f /usr/local/var/log/*.log");

        return 1;
    }
}
