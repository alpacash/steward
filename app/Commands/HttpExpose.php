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
        $connection = new \React\Socket\Connector($loop);

        // Connect to the stew.sh proxy
        $connection->connect('127.0.0.1:8085')->then(function (ConnectionInterface $connection) use ($loop) {
            $this->output->note("Connected to " . $connection->getRemoteAddress());
            $connection->on('data', function ($response) use ($connection) {
                echo $response;
//                $this->output->write($response);
//                $this->forward($response, $connection);
                $connection->close();
            });
        });

        $loop->run();

        return 1;
    }

    /**
     * @param string                            $request
     * @param \React\Socket\ConnectionInterface $respondTo
     */
    protected function forward(string $request, ConnectionInterface $respondTo)
    {
        $loop = \React\EventLoop\Factory::create();
        $out = new \React\Socket\Connector($loop);

        $out->connect('127.0.0.1:80')->then(function (ConnectionInterface $out) use ($loop, $request, $respondTo) {
            $out->write($request);
            $out->on('data', function ($response) use ($out, $respondTo) {
                $respondTo->write($response);
                $out->close();
            });
        });

        $loop->run();
    }
}
