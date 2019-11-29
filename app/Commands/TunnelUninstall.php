<?php

namespace App\Commands;

use App\LocalTunnel;
use LaravelZero\Framework\Commands\Command;

class TunnelUninstall extends Command
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'tunnel:uninstall';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete the http tunnel source code';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new LocalTunnel())->uninstall();

        $this->output->success("The localtunnel source code was permanently removed.");

        return 0;
    }
}
