<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use React\Socket\ConnectionInterface;

class HttpExpose extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:expose';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Expose your site to the internetz';

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

        // Connect to the stew.sh proxy
        $socket->connect('127.0.0.1:8090')->then(function (ConnectionInterface $connection) use ($loop) {
            $this->output->note("Connected to " . $connection->getRemoteAddress());
            $connection->on('data', function ($request) use ($connection) {
                $this->output->write($request);

                // When we receive data from the socket it is forwarded http request.
                // So we will forward this request to our local webserver and then reply with
                // the webserver's response.
                $this->forward($request, $connection);
            });
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
        $loop = \React\EventLoop\Factory::create();
        $out = new \React\Socket\Connector($loop);

        $out->connect('127.0.0.1:80')->then(function (ConnectionInterface $out) use ($loop, $request, $socket) {
            $out->write($request);
            $this->output->note("Successfully connected to the webserver...");
            $out->on('data', function ($response) use ($out, $socket) {
                echo $response;
                // Close connection with the webserver
                $out->close();

                // Forward webserver response to the socket
                $socket->write($response);
            });
        });

        $loop->run();
    }
}
