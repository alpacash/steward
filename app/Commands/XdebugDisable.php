<?php

namespace App\Commands;

use App\Extensions\Xdebug;
use App\PhpServer;
use LaravelZero\Framework\Commands\Command;

class XdebugDisable extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'xdebug:disable {--r|restart}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Disable xdebug';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $server = new PhpServer();
        $xdebug = new Xdebug($server);

        if ($xdebug->disabled()) {
            $this->output->comment("Xdebug is already disabled!");

            return;
        }

        $xdebug->disable();
        $this->output->success("[OFF] XDebug is now disabled on php {$server->version()}");

        if ($this->option('restart')) {
            $this->output->note("Restarting php service...");
            $server->restart();
        }
    }
}
