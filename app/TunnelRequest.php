<?php

namespace App;

use Psr\Http\Message\RequestInterface;

class TunnelRequest
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
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * TunnelPackage constructor.
     *
     * @param array                              $serverParams
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function __construct(
        array $serverParams,
        RequestInterface $request
    ) {
        $this->client = $serverParams['REMOTE_ADDR'] ?? '';
        $this->port = $serverParams['REMOTE_PORT'] ?? '';
        $this->request = $request;
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return serialize($this);
    }
}
