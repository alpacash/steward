<?php

namespace App\Commands;

use App\Exceptions\TunnelExceptionHandler;
use LaravelZero\Framework\Commands\Command;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\Server as SocketServer;

class ExposeListen extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'expose:listen {address=0.0.0.0:80}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start http proxy listener.';

    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $socketConnection;

    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $httpConnection;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->listen();

        return 0;
    }

    /**
     * @return void
     */
    protected function listen()
    {
        $loop = Factory::create();

        $tunnel = (new \App\Tunnel\HttpTunnel(
            $this->argument('address')
        ))->setErrorHandler(
            new TunnelExceptionHandler($this->output)
        )->listen($loop);

        $socket = new SocketServer("0.0.0.0:8090", $loop);
        $socket->on('connection', function (ConnectionInterface $server) use ($socket, $tunnel) {
            $tunnel->addServer($server);
        });

        $loop->run();
    }
}
