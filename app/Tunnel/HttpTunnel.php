<?php

namespace App\Tunnel;

use App\Tunnel\Client\ClientPool;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
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
     * HttpTunnel constructor.
     */
    public function __construct()
    {
        $this->clientpool = new ClientPool();
    }

    /**
     * @param \React\EventLoop\LoopInterface $loop
     * @param callable|null                  $proceed
     *
     * @return $this
     */
    public function listen(LoopInterface $loop, callable $proceed = null)
    {
        $http = new HttpServer(
            function (ServerRequestInterface $request) use ($proceed) {

                if (is_callable($proceed)) {
                    $proceed($request);
                }

                $static = $request->getUri()->withScheme('');
                $host = (string)$static->withPath('')->withQuery('');
                $request = new TunnelRequest(
                    Str::random(20),
                    $request->getServerParams(),
                    $request->withHeader('Host', $host)
                );

                // Wait for the server to send back our web page...
                return new Promise(function ($resolve, $reject) use ($request) {

                    $connection = $this->clientpool->nextConnectionForRequest($request, $resolve, $reject);

                    if (empty($connection)) {
                        return $resolve(
                            new Response(500, [], "There is no connection available for this request.")
                        );
                    }

                    // Send data to this specific connection
                    // and release it when we finish
                    $connection->write((string)$request);
                });
            }
        );

        $http->on('error', $this->errorHandler);

        $http->listen(new SocketServer('0.0.0.0:8091', $loop));

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
