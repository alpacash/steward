<?php

namespace App\Commands;

use App\CaddyConfig;
use App\CaddyServer;
use LaravelZero\Framework\Commands\Command;

class RestartCaddyCommand extends Command
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
    public function handle()
    {
        $this->output->success("Restarting caddy http server...");
        $server = new CaddyServer();
        (new CaddyConfig($server))->write();

        $server->restart();
    }
}
