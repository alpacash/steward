<?php

namespace App;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Server as SocketServer;
use function RingCentral\Psr7\parse_response;

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

                $tunnel = new TunnelRequest(
                    $request->getServerParams(),
                    $request->withRequestTarget($target)->withHeader('Host', $host)
                );

                $server = $this->requestOwner($tunnel);

                if (empty($server) || ! $server->isWritable()) {
                    return new Response(500, [], "There is no controller available for this request.");
                }

                $server->write(\GuzzleHttp\Psr7\str($request));

                // Wait for the server to send back our web page...
                return new Promise(function ($resolve, $reject) use ($server) {
                    $buffer = new Buffer();

                    $server->on('data', function ($chunk) use ($resolve, $reject, $buffer) {
                        $buffer->add($chunk);
//                        if ($chunk === "====stew-proceed====") {
//                            return $resolve(new Response(200), [], "Thank you, server.");
//                        }
//
//                        $response = \GuzzleHttp\Psr7\parse_response($chunk);
//
//                        if (empty($response)) {
//                            return $reject();
//                        }
//
//                        try {
//                            return $resolve($response);
//                        } catch (\Exception $e) {
//                            return $reject();
//                        }
                    });

                    $server->on('end', function() use ($resolve, $buffer) {
                        $resolve(\GuzzleHttp\Psr7\parse_response($buffer->read()));
                    });
//
//                    $server->on('error', function() use ($resolve, $buffer) {
//                        $resolve(new Response(500, [], $buffer->read()));
//                    });
//
//                    $server->on('close', function() use ($resolve, $buffer) {
//                        $resolve(new Response(200, [], $buffer->read()));
//                    });
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
