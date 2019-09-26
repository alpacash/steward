<?php

namespace App\Commands;

use App\Exceptions\TunnelExceptionHandler;
use LaravelZero\Framework\Commands\Command;
use Psr\Http\Message\RequestInterface;
use React\EventLoop\Factory;
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
        $this->listen();

        return 0;
    }

    /**
     * @return void
     */
    protected function listen()
    {
        $loop = Factory::create();

        $tunnel = (new \App\Tunnel\HttpTunnel())->setErrorHandler(
            new TunnelExceptionHandler($this->output)
        )->listen($loop, function(RequestInterface $request) {
            $this->output->note("Listening to new http request to " . $request->getUri()->getHost());
        });

        $socket = new SocketServer("0.0.0.0:8090", $loop);
        $socket->on('connection', function (ConnectionInterface $server) use ($socket, $tunnel) {

            $this->output->note("New connection from {$server->getRemoteAddress()} => "
                . $server->getLocalAddress());

            $tunnel->addServer($server);
        });

        $loop->run();
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
