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
        . "{--p|ports=32000:3306} {--P|ssh-port=22} {--R|reverse}';

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
        $sshPort = $this->option('ssh-port');
        $direction = $this->option('reverse') ? '-R' : '-L';
        $symbol = $this->option('reverse') ? '<=' : '=>';

        list ($localPort, $remotePort) = explode(":", $this->option('ports')) + [32000, 3306];

        $this->output->success("Starting tunnel 127.0.0.1:{$localPort} {$symbol} {$connection}:{$remotePort}");

        Shell::cmd("ssh -p {$sshPort} -N {$direction} "
            . "{$localPort}:127.0.0.1:{$remotePort} {$connection}", $this->output, null);
    }
}
