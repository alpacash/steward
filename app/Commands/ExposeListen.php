<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use React\Socket\ConnectionInterface;
use React\Socket\Server as SocketServer;

class ExposeListen extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'expose:listen';

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
        $loop = \React\EventLoop\Factory::create();
        $httpServer = new SocketServer("0.0.0.0:8091", $loop);
        $httpServer = new \React\Socket\LimitingServer($httpServer, 100, true);

        $socket = new SocketServer("0.0.0.0:8090", $loop);

        $this->output->note("Listening on " . $socket->getAddress() . " => " . $httpServer->getAddress());

        $socket->on('connection', function (ConnectionInterface $socketConnection) use ($socket, $httpServer) {
            $this->socketConnection = $socketConnection;
            $this->output->note("New connection from {$socketConnection->getRemoteAddress()} => "
                . $socketConnection->getLocalAddress());

            // Response from local webserver
            $socketConnection->on('data', function ($chunk) use ($socketConnection) {
                if (!$this->httpConnection) {
                    return;
                }

                $this->output->comment("Incoming response from local webserver...");
                $this->httpConnection->write($chunk);
            });
        });

        $httpServer->on('connection', [$this, 'httpConnection']);

        $loop->run();

        return 1;
    }

    /**
     * @param \React\Socket\ConnectionInterface $httpConnection
     */
    public function httpConnection(ConnectionInterface $httpConnection)
    {
        $this->httpConnection = $httpConnection;

        $this->output->note("Http request from {$httpConnection->getRemoteAddress()} => "
            . $httpConnection->getLocalAddress());

        // Request from outside
        $httpConnection->on('data', function ($rawRequest) use ($httpConnection) {
            if (!$this->socketConnection) {
                $this->output->warning("No open tunnel found, ignoring request.");
                $httpConnection->end();
                $httpConnection->close();

                return;
            }

            $this->socketConnection->write($rawRequest);
        });
    }
}
