<?php

namespace App\Tunnel;

use function GuzzleHttp\Psr7\str;
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
     * @var string
     */
    protected $id;

    /**
     * TunnelPackage constructor.
     *
     * @param string                             $id
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function __construct(
        string $id,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->id = md5($id . str($request));
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
    public function getId(): string
    {
        return $this->id;
    }

    public function __sleep()
    {
        $this->request = serialize($this->request);

        return ['client', 'port', 'id', 'request'];
    }

    public function __wakeup()
    {
        $this->request = unserialize($this->request);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        unset ($this->resolver);
        unset ($this->rejecter);

        return serialize($this);
    }
}
