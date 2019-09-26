<?php

namespace App;

abstract class TunnelMessage
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
     * @var string
     */
    protected $id;

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
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return serialize($this);
    }
}
