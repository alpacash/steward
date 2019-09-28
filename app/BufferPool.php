<?php

namespace App;

class BufferPool
{
    /**
     * @var \App\Buffer[]
     */
    protected $buffers = [];

    /**
     * @param \App\TunnelRequest $request
     *
     * @return \App\Buffer
     */
    public function create(TunnelRequest $request)
    {
        return $this->buffers[$request->getId()] = new Buffer();
    }

    /**
     * @param $chunk
     *
     * @return \App\Buffer
     */
    public function chunk($chunk)
    {
        $header = substr(substr($chunk, 0, strpos($chunk, "===\r\n")), 3);
        $requestId = last(explode(':', $header));

        if (!isset($this->buffers[$requestId])
            || !$this->buffers[$requestId] instanceof Buffer) {
            return new Buffer();
        }

        return $this->buffers[$requestId]->chunk($chunk);
    }
}
