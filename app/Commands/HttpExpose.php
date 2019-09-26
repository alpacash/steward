<?php

namespace App\Commands;

use App\Tunnel\TunnelRequest;
use App\Tunnel\TunnelResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\str;
use LaravelZero\Framework\Commands\Command;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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

            $connection->on('data', function ($request) use ($connection) {

                // When we receive data from the socket it is a forwarded http request.
                // So we will forward this request to our local webserver and then reply with
                // the webserver's response.
                $this->proxy(unserialize($request), $connection);
            });
        })->otherwise(function($exception) {

            /** @var \RuntimeException $exception */
            $this->verbose("\n" . $exception->getMessage());
            $this->output->error("The tunnel seems offline at this moment. Try again later.");
        });

        return $this->connections[$key] = $socket;
    }

    /**
     * @param \App\Tunnel\TunnelRequest         $tunnel
     * @param \React\Socket\ConnectionInterface $socket
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function proxy(TunnelRequest $tunnel, ConnectionInterface $socket)
    {
        $request = $tunnel->getRequest();

        echo "Request hash:\n" . md5(str($request)) . "\n";

        $originalHost = $request->getUri()->getHost();
        $this->output->note("Tunneling request " . $request->getRequestTarget()
            . " => " . $originalHost);

        $request = $request->withUri($request->getUri()->withPort(80)->withHost('127.0.0.1'), true);
        $request = $request->withUri($request->getUri()->withPort(80)->withHost(
            substr($originalHost, 0, strpos($originalHost, ':') ?: strlen($originalHost))
        ));

        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $request->withoutHeader('Content-Length');

        $this->output->note("Executing http request to local webserver => {$request->getMethod()} {$request->getUri()}");

        try {
            $response = (new Client())->send($request, [
                'http_errors' => false,
                'timeout' => 10,
                'synchronous' => true,
                'allow_redirects' => false // need this
            ]);

            $this->output->success("Executed http request to local webserver <= {$response->getStatusCode()}");

            $response = new TunnelResponse($tunnel->getId(), $response);
        } catch (\Exception $e) {
            $this->output->error("Failed http request to local webserver...\n" . $e->getMessage());

            $response = new TunnelResponse($tunnel->getId(), new Response(500, [], "Server timeout."));
        }

        echo str($request);

        // DIT HIER GAAT HET HEM DOEN
        $data = serialize($response) . "===stew-data-end===";

        $socket->write($data);
    }

    /**
     * @param string $message
     * @param bool   $raw
     */
    protected function verbose(string $message, bool $raw = false)
    {
        if ($this->getOutput()->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        $raw ? $this->output->write($message) : $this->output->comment($message);
    }
}
