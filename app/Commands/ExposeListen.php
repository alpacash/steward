<?php

namespace App\Commands;

use App\HttpTunnel;
use LaravelZero\Framework\Commands\Command;

class ExposeListen extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'expose:listen';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start http proxy listener.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $loop = \React\EventLoop\Factory::create();
        $server = new \React\Socket\LimitingServer(
            new \React\Socket\Server("0.0.0.0:8090", $loop),
            100,
            true
        );

        $this->output->note("Listening on " . $server->getAddress());

        $server->on('connection', function (\React\Socket\ConnectionInterface $connection) use ($server) {
            $this->output->note("New connection: {$connection->getRemoteAddress()}");
            $connection->write("hi there...");
            $connection->close();
            exit;
            // Webserver listening should pass everything to $client
            // and reply with appropriate response.
//            (new HttpTunnel('0.0.0.0:8080'))->respondTo($connection);
//            $connection->close();
        });

        $loop->run();

        return 1;
    }
}
