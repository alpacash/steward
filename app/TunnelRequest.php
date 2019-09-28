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
     * @var string
     */
    protected $id;

    /**
     * @var callable
     */
    protected $resolver;

    /**
     * @var callable
     */
    protected $rejecter;

    /**
     * TunnelPackage constructor.
     *
     * @param string                             $id
     * @param array                              $serverParams
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function __construct(
        string $id,
        array $serverParams,
        RequestInterface $request
    ) {
        $this->client = $serverParams['REMOTE_ADDR'] ?? '';
        $this->port = $serverParams['REMOTE_PORT'] ?? '';
        $this->request = $request;
        $this->id = md5($id . serialize($request));
        $this->resolver = function () { return true; };
        $this->rejecter = function () { return true; };
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

    /**
     * @return string
     */
    public function __toString()
    {
        unset ($this->resolver);
        unset ($this->rejecter);

        return serialize($this);
    }

    /**
     * @param callable $resolver
     *
     * @return TunnelRequest
     */
    public function setResolver(callable $resolver): TunnelRequest
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @param callable $rejecter
     *
     * @return TunnelRequest
     */
    public function setRejecter(callable $rejecter): TunnelRequest
    {
        $this->rejecter = $rejecter;

        return $this;
    }

    /**
     * @return callable
     */
    public function getResolver(): ?callable
    {
        return $this->resolver ?? null;
    }

    /**
     * @return callable
     */
    public function getRejecter(): ?callable
    {
        return $this->rejecter ?? null;
    }
}
