<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use React\Socket\ConnectionInterface;
use React\Socket\Server as SocketServer;
use Symfony\Component\Console\Output\OutputInterface;

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
        try {
            $this->listen();
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * @return void
     */
    protected function listen()
    {
        $loop = \React\EventLoop\Factory::create();
        $httpServer = new SocketServer("0.0.0.0:8091", $loop);
        $httpServer = new \React\Socket\LimitingServer($httpServer, 100, false);

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

                $this->output->note("==> Http response from {$socketConnection->getRemoteAddress()}");
                $this->httpConnection->write($chunk);
            });
        });

        $httpServer->on('connection', [$this, 'httpConnection']);

        $loop->run();
    }

    /**
     * @param \React\Socket\ConnectionInterface $httpConnection
     */
    public function httpConnection(ConnectionInterface $httpConnection)
    {
        $this->httpConnection = $httpConnection;

        $this->output->note("<== Http request from {$httpConnection->getRemoteAddress()} => "
            . $httpConnection->getLocalAddress());

        // Request from outside
        $httpConnection->on('data', function ($rawRequest) use ($httpConnection) {
            if (!$this->socketConnection || !$this->socketConnection->isWritable()) {
                $this->output->warning("No open tunnel found, ignoring request.");
                $httpConnection->end();
                $httpConnection->close();

                if ($this->socketConnection instanceof ConnectionInterface) {
                    $this->socketConnection->end();
                    $this->socketConnection->close();
                }

                return;
            }

            $this->socketConnection->write($rawRequest);
        });
    }

    /**
     * @param string $message
     */
    protected function verbose(string $message)
    {
        if ($this->getOutput()->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        $this->output->comment($message);
    }
}
