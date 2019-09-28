<?php

namespace App;

use function GuzzleHttp\Psr7\parse_response;
use function GuzzleHttp\Psr7\str;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

class TunnelResponse
{
    /**
     * @var string
     */
    protected $client;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $requestId;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * TunnelPackage constructor.
     *
     * @param string                              $requestId
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(
        string $requestId,
        ResponseInterface $response
    ) {
        $this->requestId = $requestId;
        $this->response = $response;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return serialize($this);
    }

    public function __sleep()
    {
        if ($this->response instanceof MessageInterface) {
            $this->response = str($this->response);
        }

        return ['client', 'port', 'requestId', 'response'];
    }

    public function __wakeup()
    {
        if (!empty($this->response)) {
            $this->response = parse_response($this->response);
        }
    }
}
