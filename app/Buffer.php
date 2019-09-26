<?php

namespace App;

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
    public function add(string $data)
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
     * @return string
     */
    public function read()
    {
        return $this->buffer;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->buffer;
    }
}
