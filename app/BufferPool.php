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
}
