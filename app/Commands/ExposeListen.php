<?php

namespace App\Commands;

use App\TcpProxy;
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
            new \React\Socket\Server("0.0.0.0:8085", $loop),
            100,
            true
        );

        $this->output->note("Listening on " . $server->getAddress());

        $server->on('connection', function (\React\Socket\ConnectionInterface $client) {
            $this->output->note("New connection: {$client->getRemoteAddress()}");
            // Webserver listening should pass everything to $client
            // and reply with appropriate response.
            (new TcpProxy($client, '0.0.0.0:8080'))->listen();
            $client->close();
        });

        $loop->run();

        return 1;
    }
}
