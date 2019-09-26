<?php

namespace App\Commands;

use App\Buffer;
use App\HttpRequest;
use LaravelZero\Framework\Commands\Command;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HttpExpose extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:expose {--localhost}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Expose your site to the internetz';

    /**
     * @var \App\Buffer
     */
    protected $buffer;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->note("Exposing local dev environments");

        $loop = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Connector($loop);

        $listen = ($this->option('localhost') ? '127.0.0.1' : 'stew.sh') . ":8090";

        // Connect to the stew.sh proxy
        $socket->connect($listen)->then(function (ConnectionInterface $connection) use ($loop) {

            $this->output->note("Connected to " . $connection->getRemoteAddress());
            $connection->on('data', function ($request) use ($connection) {

                $this->verbose("Incoming request from outside...");

                // When we receive data from the socket it is forwarded http request.
                // So we will forward this request to our local webserver and then reply with
                // the webserver's response.
                $this->forward($request, $connection);
            });
        })->otherwise(function($exception) {

            /** @var \RuntimeException $exception */
            $this->output->error("The tunnel seems offline at this moment. Try again later."
                . $this->verbose("\n" . $exception->getMessage()));
        });

        $loop->run();

        return 1;
    }

    /**
     * @param string                            $request
     * @param \React\Socket\ConnectionInterface $socket
     */
    protected function forward(string $request, ConnectionInterface $socket)
    {
        $request = $this->tamper($request);

        $loop = \React\EventLoop\Factory::create();
        $webserver = new \React\Socket\Connector($loop);
        $webserver->connect('127.0.0.1:80')
            ->then(function (ConnectionInterface $webserver) use ($loop, $request, $socket) {

                $this->buffer = new Buffer();

                $this->output->writeln(HttpRequest::raw($request)->logFormat());
                $this->verbose("Successfully connected to the webserver...");

                $webserver->write($request);
                $webserver->on('data', function ($response) use ($webserver, $socket) {

                    // Forward webserver response to the socket
                    $this->buffer->add($response);

                    // Was this the last chunk?
                    if (substr(trim($response), -1) === '0') {
                        $socket->write($this->buffer->read());
                        $webserver->close();
                    }
                });
            });

        $loop->run();
    }

    /**
     * @param string $request
     *
     * @return string|string[]|null
     */
    protected function tamper(string $request)
    {
        // Remove port from the headers.
        $request = preg_replace('/^(Host)\: (.+)(\:\d+)/im', 'Host: $2', $request);

        return $request;
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
