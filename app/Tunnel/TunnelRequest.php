<?php

namespace App\Tunnel;

use function GuzzleHttp\Psr7\parse_request;
use function GuzzleHttp\Psr7\str;
use Psr\Http\Message\ServerRequestInterface;

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
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $id;

    /**
     * TunnelPackage constructor.
     *
     * @param string                                   $id
     * @param array                                    $serverParams
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(
        string $id,
        array $serverParams,
        ServerRequestInterface $request
    ) {
        $this->client = $serverParams['REMOTE_ADDR'] ?? '';
        $this->port = $serverParams['REMOTE_PORT'] ?? '';
        $this->request = $request;
        $this->id = md5($id . str($request));
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
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
