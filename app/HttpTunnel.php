<?php

namespace App;

use React\Socket\ConnectionInterface;

class HttpTunnel
{

    /**
     * @var string
     */
    protected $listen;

    /**
     * SocketProxy constructor.
     *
     * @param string $listen
     */
    public function __construct(
        string $listen
    ) {
        $this->listen = $listen;
    }

    /**
     * @param \React\Socket\ConnectionInterface $respondTo
     *
     * @return void
     */
    public function respondTo(ConnectionInterface $respondTo)
    {
        $respondTo->write('Hi!');
        $respondTo->close();
        return;
        $loop = \React\EventLoop\Factory::create();
        $server = new \React\Socket\LimitingServer(
            new \React\Socket\Server($this->listen, $loop),
            100,
            true
        );

        $server->on('connection', function (ConnectionInterface $browser) use ($respondTo) {
            $respondTo->write('Hi');
            $respondTo->close();
            $browser->close();
//            $browser->on('data', function ($request) use ($browser, $respondTo) {
//                echo $request;
//
//                $respondTo->on('data', function ($response) use ($request, $browser, $respondTo) {
//                    echo $response;
//                     Close the socket.
//                    $respondTo->close();
//                     Reply to the browser.
//                    $browser->write($response);
//                     Close the http request w/ te browser.
//                    $browser->close();
//                });
//
//                echo "requesting...";
//                $respondTo->write($request);
//            });
        });

        $loop->run();
    }
}
