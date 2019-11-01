<?php

namespace App\Commands;

use App\Shell;
use LaravelZero\Framework\Commands\Command;

class SshTunnel extends Command
{

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'ssh:tunnel {connection : E.g. sshuser@yourserver.com} "
        . "{--p|remote-port=3306} {--l|local-port=32000} {--P|ssh-port=22}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Open an ssh tunnel to access any services on your remote server.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $connection = $this->argument('connection');
        $remotePort = $this->option('remote-port');
        $localPort = $this->option('local-port');
        $sshPort = $this->option('ssh-port');

        $this->output->success("Starting tunnel 127.0.0.1:{$localPort} => {$connection}:{$remotePort}");

        Shell::cmd("ssh -p {$sshPort} -N -L "
            . "{$localPort}:127.0.0.1:{$remotePort} {$connection}", $this->output, null);
    }
}
