<?php

namespace App\Tunnel;

use App\Tunnel\TunnelResponse;
use GuzzleHttp\Psr7\Response;

class Buffer
{
    /**
     * @var string
     */
    protected $buffer;

    /**
     * Buffer constructor.
     *
     * @param string $start
     */
    public function __construct($start = '')
    {
        $this->buffer = $start;
    }

    /**
     * @param string $data
     *
     * @return self
     */
    public function chunk(string $data)
    {
        $this->buffer .= str_replace('===stew-data-end===', '', $data);

        return $this;
    }

    /**
     * @return self
     */
    public function clear()
    {
        $this->buffer = '';

        return $this;
    }

    /**
     * @param bool $clear
     *
     * @return string
     */
    public function read($clear = false)
    {
        $b = $this->buffer;

        if ($clear) {
            $this->clear();
        }

        return $b;
    }

    /**
     * @return \App\Tunnel\TunnelResponse
     */
    public function tunnelResponse()
    {
        try {
            $response = $this->read();

            /** @var \App\Tunnel\TunnelResponse $s */
            $s = unserialize($response);

            return $s;
        } catch (\Exception $e) {
            return new TunnelResponse(str_random(12), new Response(200, [], $response . " " . $e->getMessage()));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->buffer;
    }

    /**
     * @param string $contentLength
     *
     * @return bool
     */
    public function reached(?string $contentLength)
    {
        if ($contentLength === null) {
            return false;
        }

        return strlen($this->buffer) >= $contentLength;
    }
}
