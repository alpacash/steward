<?php

namespace App\Tunnel;

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
    public function read($clear = true)
    {
        $b = $this->buffer;

        if ($clear) {
            $this->clear();
        }

        return str_replace('===stew-data-end===', '', $b);
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
