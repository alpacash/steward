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
    protected $signature = 'xdebug:enable';

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
//        $server->restart();

        $this->output->success("Done!");
    }
}
