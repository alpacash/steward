<?php

namespace App;

use React\Socket\ConnectionInterface;

class TcpProxy
{

    /**
     * @var string
     */
    protected $listen;

    /**
     * @var \React\Socket\ConnectionInterface
     */
    protected $respondTo;

    /**
     * SocketProxy constructor.
     *
     * @param \React\Socket\ConnectionInterface $respondTo
     * @param string                            $listen
     */
    public function __construct(
        ConnectionInterface $respondTo,
        string $listen
    ) {
        $this->respondTo = $respondTo;
        $this->listen = $listen;
    }

    /**
     * @return void
     */
    public function listen()
    {
        $loop = \React\EventLoop\Factory::create();
        $server = new \React\Socket\LimitingServer(
            new \React\Socket\Server($this->listen, $loop),
            100,
            true
        );

        $server->on('connection', function (ConnectionInterface $in) {
            $in->on('data', function ($data) use ($in) {
                $this->respondTo->on('data', function ($data) use ($in) {
                    $in->write($data);
                });

                // Write the results to the socket.
                $this->respondTo->write($data);

                // Close the socket.
                $this->respondTo->close();

                // Close the http request w/ te browser.
                $in->close();
            });
        });

        $loop->run();
    }
}
