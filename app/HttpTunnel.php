<?php

namespace App;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Server as SocketServer;

class HttpTunnel
{
    /**
     * @var ConnectionInterface[]
     */
    protected $connections = [];

    /**
     * @var callable
     */
    protected $errorHandler;

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
                $target = (string)$static->withHost('')->withPort(null)
                    ?: $request->getMethod() === 'OPTIONS' ? '*' : '/';

                $request = new TunnelRequest(
                    $request->getServerParams(),
                    $request->withRequestTarget($target)->withHeader('Host', $host)
                );

                $server = $this->requestOwner($request);

                if (empty($server) || ! $server->isWritable()) {
                    return new Response(500, [], "There is no controller available for this request.");
                }

                $server->write((string)$request);

                // Wait for the server to send back our web page...
                return new Promise(function ($resolve, $reject) use ($server) {
                    $server->once('data', function ($data) use ($resolve, $reject) {
                        try {
                            $resolve(\GuzzleHttp\Psr7\parse_response($data));
                        } catch (\Exception $e) {
                            $reject();
                        }
                    });
                });
            }
        );

        $http->on('error', $this->errorHandler);

        $http->listen(new SocketServer('0.0.0.0:8091', $loop));

        return $this;
    }

    /**
     * @param string              $key
     * @param ConnectionInterface $connection
     */
    public function addServer(string $key, ConnectionInterface $connection)
    {
        $this->connections[$key] = $connection;
    }

    /**
     * @param string $key
     *
     * @return self
     */
    public function removeServer(string $key)
    {
        if (isset($this->connections[$key])) {
            unset($this->connections[$key]);
        }

        return $this;
    }

    /**
     * @param \App\TunnelRequest $request
     *
     * @return ConnectionInterface|null
     */
    public function requestOwner(TunnelRequest $request)
    {
        /** @var ConnectionInterface $server */
        $server = current($this->connections ?: []);

        if (! $server instanceof ConnectionInterface) {
            return null;
        }

        if (is_callable($this->errorHandler)) {
            $server->on('error', $this->errorHandler);
        }

        return $server;
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
