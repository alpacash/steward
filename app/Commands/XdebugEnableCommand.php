<?php

namespace App\Commands;

use App\Extensions\Xdebug;
use App\PhpServer;
use LaravelZero\Framework\Commands\Command;

class XdebugEnableCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'xdebug:enable {--restart}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Enable xdebug';

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

        if ($xdebug->enabled()) {
            $this->output->comment("Xdebug is already enabled!");

            return;
        }

        $xdebug->enable();
        $this->output->success("[ON] XDebug is now enabled on php {$server->version()}");

        if ($this->option('restart')) {
            $this->output->note("Restarting php service...");
            $server->restart();
        }
    }
}
