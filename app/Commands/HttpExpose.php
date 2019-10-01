<?php

namespace App\Commands;

use App\Tunnel\TunnelRequest;
use App\Tunnel\TunnelResponse;
use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\parse_request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Psr\Http\Message\RequestInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HttpExpose extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'http:expose {--localhost}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Expose your site to the internetz';

    /**
     * @var \React\Socket\ConnectionInterface[]
     */
    protected $connections = [];

    /**
     * @var bool
     */
    protected $failed = false;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->note("Exposing local dev environments");

        $loop = \React\EventLoop\Factory::create();

        for ($i=0; $i < 10; $i++) {
            $this->createConnection('conn-' . $i, $loop);
        }

        $loop->run();

        return 0;
    }

    /**
     * @param string                         $key
     * @param \React\EventLoop\LoopInterface $loop
     *
     * @return \React\Socket\Connector
     */
    protected function createConnection(string $key, LoopInterface $loop)
    {
        $socket = new \React\Socket\Connector($loop, ['timeout' => 10]);

        $listen = ($this->option('localhost') ? '127.0.0.1' : 'stew.sh') . ":8090";

        // Connect to the stew.sh proxy
        $socket->connect($listen)->then(function (ConnectionInterface $connection) use ($loop) {

            $this->output->note("Connected to " . $connection->getRemoteAddress());

            $connection->on('data', function ($raw) use ($connection, &$buffer) {

                // When we receive data from the socket it is a forwarded http request.
                // So we will forward this request to our local webserver and then reply with
                // the webserver's response.

                $this->proxy(parse_request($raw), $connection);
            });
        })->otherwise(function() {

            if ($this->failed) {
                return;
            }

            /** @var \RuntimeException $exception */
            $this->output->error("The tunnel seems offline at this moment. Try again later.");

            $this->failed = true;
        });

        return $this->connections[$key] = $socket;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \React\Socket\ConnectionInterface  $socket
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function proxy(RequestInterface $request, ConnectionInterface $socket)
    {

        $originalHost = $request->getUri()->getHost();
        $this->output->note("Tunneling request " . $request->getRequestTarget()
            . " => " . $originalHost);

        $request = $request->withUri($request->getUri()->withPort(80)->withHost(
            str_replace(':8091', '', $request->getUri()->getHost())
        ));

        $request = $request->withUri($request->getUri()->withPort(80)->withHost(
            str_replace($request->getUri()->getHost(), '127.0.0.1', $request->getUri()->getHost())
        ), true);

        if ($request->getHeader('Referer')) {
            $request = $request->withHeader('Referer' ,
                str_replace(':8091', '', $request->getHeader('Referer')));
        }

        if ($request->getHeader('Origin')) {
            $request = $request->withHeader('Origin' ,
                str_replace(':8091', '', $request->getHeader('Origin')));
        }

        $this->output->note("Executing http request to local webserver => {$request->getMethod()} {$request->getUri()}");

        try {
            $response = (new Client())->send($request, [
                'http_errors' => false,
                'timeout' => 60,
                'synchronous' => true,
                'allow_redirects' => false // need this
            ]);

            $this->output->success("<= Executed http request to local webserver {$response->getStatusCode()}");
        } catch (\Exception $e) {
            $this->output->error("Failed http request to local webserver...\n" . $e->getMessage());

            $response = new Response(500, [], "Server timeout.");
        }

        // DIT HIER GAAT HET HEM DOEN
        $data = \GuzzleHttp\Psr7\str(
            $response->withoutHeader('Transfer-Encoding')
        ) . "===stew-data-end===";

        $socket->write($data);

        $this->output->success("=> Successfully wrote response {$response->getStatusCode()} to the socket...");
    }
}
