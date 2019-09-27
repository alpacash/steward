<?php

namespace App\Commands;

use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\str;
use LaravelZero\Framework\Commands\Command;
use Psr\Http\Message\RequestInterface;
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
     * @var \App\Buffer
     */
    protected $buffer;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->note("Exposing local dev environments");

        $loop = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Connector($loop, ['timeout' => 10]);

        $listen = ($this->option('localhost') ? '127.0.0.1' : 'stew.sh') . ":8090";

        // Connect to the stew.sh proxy
        $socket->connect($listen)->then(function (ConnectionInterface $connection) use ($loop) {

            $this->output->note("Connected to " . $connection->getRemoteAddress());

            $connection->on('data', function ($request) use ($connection) {

                $this->verbose("Incoming request from outside...");

                // When we receive data from the socket it is a forwarded http request.
                // So we will forward this request to our local webserver and then reply with
                // the webserver's response.
                $this->proxy(\GuzzleHttp\Psr7\parse_request($request), $connection);
            });
        })->otherwise(function($exception) {

            /** @var \RuntimeException $exception */
            $this->verbose("\n" . $exception->getMessage());
            $this->output->error("The tunnel seems offline at this moment. Try again later.");
            $this->stop = true;
        });

        $loop->run();

        if (empty($this->stop)) {
            $this->handle();
        }

        return 0;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \React\Socket\ConnectionInterface  $socket
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function proxy(RequestInterface $request, ConnectionInterface $socket)
    {
        echo "Request hash:\n" . md5(str($request)) . "\n";

        $this->output->note("Tunneling request " . $request->getRequestTarget()
            . " => " . $request->getUri()->getHost());

        $headers = $request->getHeaders();
        $originalHost = $headers['Host'][0];
        $headers['Host'][0] = substr($originalHost, 0, strpos($originalHost, ':') ?: strlen($originalHost));

        try {
            $this->output->note("Executing http request to local webserver => {$request->getUri()}");
            $uri = $request->getUri();
            $uri = $uri->getScheme() . "://" . $uri->getHost() . $uri->getPath() . $uri->getQuery();

            echo (string)$uri;
            $response = (new Client())->request(
                $request->getMethod(),
                    $uri, [
                    'http_errors' => false,
                    'body' => $request->getBody(),
                    'headers' => $headers,
                    'version' => $request->getProtocolVersion()
                ]
            );
            $this->output->success("Executed http request to local webserver <= {$response->getStatusCode()}");
            $socket->end(\GuzzleHttp\Psr7\str($response));
            $this->output->success("Socket closed... New cycle...");
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
            $socket->write('====stew-proceed====');

            return;
        }
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
