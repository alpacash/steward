<?php

namespace App\Commands;

use App\Buffer;
use App\HttpRequest;
use App\HttpResponse;
use Illuminate\Support\Str;
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
        $socket = new \React\Socket\Connector($loop, ['timeout' => 10]);

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
            $this->verbose("\n" . $exception->getMessage());
            $this->output->error("The tunnel seems offline at this moment. Try again later.");

            return 1;
        });

        $loop->run();

        return 0;
    }

    /**
     * @param string                            $request
     * @param \React\Socket\ConnectionInterface $socket
     */
    protected function forward(string $request, ConnectionInterface $socket)
    {
        $request = $this->tamper($request);

        $loop = \React\EventLoop\Factory::create();
        $webserver = new \React\Socket\Connector($loop, ['timeout' => 30]);
        $webserver->connect('127.0.0.1:80')
            ->then(function (ConnectionInterface $webserver) use ($request, $socket) {

                $this->buffer = new Buffer();
                $httpRequest = HttpRequest::raw($request);
                $this->output->writeln($httpRequest->logFormat());
                $this->verbose("\n" . $request, true);

                $webserver->write($request);

                $webserver->on('data', function ($chunk) use ($webserver, $socket) {
                    // Forward webserver response to the socket
                    $this->buffer->add($chunk);
                    $socket->write($chunk);
//                    $this->output->writeln("Next chunk...\n" . substr($chunk, 0, 50));

                    // Was this the last chunk?
                    if (stristr($chunk, "Not Modified") || $this->buffer->reached($contentLength ?? null) || Str::endsWith($chunk, "0\r\n\r\n")) {
                        $webserver->end();
                        $webserver->close();

                        $this->output->writeln(HttpResponse::raw($this->buffer->read())->logFormat());

                        $this->buffer->clear();
                    }
                });
            })->otherwise(function($exception) {

                /** @var \RuntimeException $exception */
                $this->verbose("\n" . $exception->getMessage());
                $this->output->error("The webserver offline?");

                return 1;
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
     * @param bool   $raw
     */
    protected function verbose(string $message, bool $raw = false)
    {
        if ($this->getOutput()->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        $raw ? $this->output->write($message) : $this->output->comment($message);
    }
}
