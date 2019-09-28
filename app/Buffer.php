<?php

namespace App;

use function GuzzleHttp\Psr7\parse_request;
use function GuzzleHttp\Psr7\parse_response;
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
        $this->buffer .= $data;

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
     * @return false|\App\TunnelResponse
     */
    public function tunnelResponse()
    {
        try {
            $response = $this->read();
            $response = substr($response, strpos($response, "===\r\n") + 5);

            echo substr($response, 0, 400) . "\n\n";

            /** @var \App\TunnelResponse $s */
            $s = unserialize($response);

            echo "aaaaa\n" . $s->getRequestId() . "\n";

            return $s;
        } catch (\Exception $e) {
            return new TunnelResponse(str_random(12), new Response(200, [], $e->getMessage()));
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
