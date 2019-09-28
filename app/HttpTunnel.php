<?php

namespace App;

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
     * @var callable[][]
     */
    protected $connections = [];

    /**
     * @var callable
     */
    protected $errorHandler;

    /**
     * @var \App\BufferPool
     */
    protected $bufferpool;

    /**
     * HttpTunnel constructor.
     */
    public function __construct()
    {
        $this->bufferpool = new BufferPool();
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
                $target = (string)$static->withHost('')->withPort(null)
                    ?: $request->getMethod() === 'OPTIONS' ? '*' : '/';

                $request = new TunnelRequest(
                    Str::random(20),
                    $request->getServerParams(),
                    $request->withRequestTarget($target)->withHeader('Host', $host)
                );

                // Wait for the server to send back our web page...
                return new Promise(function ($resolve, $reject) use ($request) {

                    $controller = $this->findControllerForRequest($request);

                    if (empty($controller) || ! $controller->isWritable()) {
                        return $resolve(
                            new Response(500, [], "There is no controller available for this request.")
                        );
                    }

                    $buffer = $this->bufferpool->create($request);

                    // When we receive a chunk of data, we will send it to the appropriate buffer.
                    $controller->on('data', function ($chunk) {

                        // =============== ALERT ALERT ALERT ALERT ===========================
                        // Buffer is alleen voor dit request, maar de data misschien niet...
                        // We moeten hier zien te achterhalen voor welk request de data is en
                        // dus op welke buffer die hoort...
                        $this->bufferpool->chunk($chunk);
                    });

                    $controller->on('error', function(\Exception $e) use ($reject, $buffer) {
                        $buffer->clear();
                        return $reject(new Response(500, [], $e->getMessage()));
                    });

                    // This response's destination could be anything we asked for,
                    // thus we have to find out what it is for.
                    $controller->on('end', function() use ($resolve, $buffer) {
                        // return $resolver(new Response(200));
                        echo "end\n";
                        return $resolve($buffer->tunnelResponse()->getResponse());
                    });

                    $controller->on('close', function() use ($buffer, $request, $resolve) {
                        echo "close\n";
                        // return $resolver(new Response(200));
                        return $resolve($buffer->tunnelResponse()->getResponse());
                    });

                    // Send the request from the browser to the local host.
                    $controller->write((string)$request);
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
        /** @var ConnectionInterface $oldserver */
        if (!empty($this->connections[$key])
            && ($oldserver = $this->connections[$key]) instanceof ConnectionInterface) {
            $oldserver->end();
            $oldserver->close();
        }

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
            $this->connections[$key]->end();
            $this->connections[$key]->close();

            unset($this->connections[$key]);
        }

        return $this;
    }

    /**
     * @param \App\TunnelRequest $request
     *
     * @return ConnectionInterface|null
     */
    public function findControllerForRequest(TunnelRequest $request)
    {
        /** @var ConnectionInterface $server */
        $controller = current($this->connections ?: []);

        if (! $controller instanceof ConnectionInterface) {
            return null;
        }

        $controller->removeAllListeners();

        return $controller;
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
