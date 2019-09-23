<?php

namespace App\Commands;

use App\PhpServer;
use LaravelZero\Framework\Commands\Command;

class SwitchPhpVersionCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'php:version {version}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Switch to another php version';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $server = new PhpServer();
        $server->useVersion($this->argument('version'));
        $this->output->success("Done! New PHP version is {$server->version()}");
    }
}
