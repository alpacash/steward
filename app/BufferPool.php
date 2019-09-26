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
    public function forRequest(TunnelRequest $request)
    {
        if (! empty($this->buffers[$request->getId()])) {
            return $this->buffers[$request->getId()];
        }

        return $this->buffers[$request->getId()] = new Buffer();
    }

    /**
     * @param \App\TunnelRequest $request
     * @param string             $chunk
     *
     * @return \App\Buffer
     */
    public function chunk(TunnelRequest $request, string $chunk)
    {
        return $this->forRequest($request)->chunk($chunk);
    }

    /**
     * @param \App\TunnelRequest $request
     */
    public function clear(TunnelRequest $request)
    {
        if (isset($this->buffers[$request->getId()])) {
            unset ($this->buffers[$request->getId()]);
        }
    }
}
