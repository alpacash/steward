<?php

namespace App;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpResponse
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * HttpRequest constructor.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(
        ?ResponseInterface $response
    ) {
        $this->response = $response;
    }

    /**
     * @param string $message
     *
     * @return self
     */
    public static function raw(string $message)
    {
        return new static(
            self::parseHttpMessage($message)
        );
    }

    /**
     * @param string $message
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    protected static function parseHttpMessage(string $message)
    {
        try {
            $message = (new RawHttp($message));
            $headers = $message->getHeaders();

            list ($version, $status) = explode(" ", current($headers)) + ['', ''];

            return new Response($status, $headers, $message->getBody());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function logFormat(): string
    {
        if (empty($this->getResponse())) {
            return '';
        }

        $response = $this->getResponse();

        $statusColor = $response->getStatusCode() === '200' ? 'green' : 'yellow';

        return "  <fg=cyan> ==> </>\t<fg=$statusColor>" . $response->getStatusCode() . "</>\t"
            . $response->getReasonPhrase() . "\t\t\t"
            . "<fg=green> => </> <fg=red>" . $response->getProtocolVersion() . "</>";
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
