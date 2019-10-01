<?php

namespace App\Tunnel;

use App\Tunnel\Client\ClientPool;
use function GuzzleHttp\Psr7\parse_request;
use function GuzzleHttp\Psr7\parse_response;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use League\CLImate\CLImate;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Middleware\LimitConcurrentRequestsMiddleware;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Server as HttpServer;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Server as SocketServer;

class HttpTunnel
{
    /**
     * @var \App\Tunnel\Client\ClientPool
     */
    protected $clientpool;

    /**
     * @var callable
     */
    protected $errorHandler;

    /**
     * @var string
     */
    protected $bind;

    /**
     * @var \League\CLImate\CLImate
     */
    protected $cli;

    /**
     * HttpTunnel constructor.
     *
     * @param string $bind
     */
    public function __construct(
        string $bind = '0.0.0.0:80'
    ) {
        $this->clientpool = new ClientPool();
        $this->bind = $bind;
        $this->cli = new CLImate();
    }

    /**
     * @param \React\EventLoop\LoopInterface $loop
     * @param callable|null                  $before
     *
     * @return $this
     */
    public function listen(LoopInterface $loop, callable $before = null)
    {
        $http = new SocketServer($this->bind, $loop);
        $http->on('connection', function (ConnectionInterface $browser) use ($http, $before) {

            $client = $this->clientpool->nextConnectionForRequest();

            if (empty($client)) {
                $browser->end(\GuzzleHttp\Psr7\str(
                    new Response(500, [], "There is no host available for this request.")
                ));
            }

            $client->resolves(function ($response) use ($browser, $client) {

                $this->cli->comment("Returning response to the browser...");

                $browser->end($response);

                $this->cli->green("Returned response to the browser...");
            });

            if (is_callable($before)) {
                $before();
            }

            $browser->on('data', function ($chunk) use ($client) {

                // When we receive a http request, we send it to the client
                // and wait for a reply to pass it over to the browser.
                $client->write($chunk);

                $this->cli->green("Wrote the browser request to the socket...");
            });
        });

        return $this;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function addServer(ConnectionInterface $connection)
    {
        $this->clientpool->addConnection($connection);
    }

    /**
     * @param callable $errorHandler
     *
     * @return HttpTunnel
     */
    public function setErrorHandler(callable $errorHandler): HttpTunnel
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }
}
