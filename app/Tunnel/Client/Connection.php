<?php

namespace App\Tunnel\Client;

use App\Tunnel\Buffer;
use Illuminate\Support\Str;
use React\Socket\ConnectionInterface;

class Connection
{
    /**
     * @var ConnectionInterface
     */
    protected $socket;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var bool
     */
    protected $busy = false;

    /**
     * @var \App\Buffer
     */
    protected $buffer;

    /**
     * @var callable
     */
    protected $reject;

    /**
     * @var callable
     */
    protected $resolve;

    /**
     * Connection constructor.
     *
     * @param string              $id
     * @param ConnectionInterface $socket
     */
    public function __construct(
        string $id,
        ConnectionInterface $socket
    ) {
        $socket->removeAllListeners();

        // When we receive a chunk of data, we will send it to the appropriate buffer.
        $socket->on('data', function ($chunk) {
            // =============== ALERT ALERT ALERT ALERT ===========================
            // Buffer is alleen voor dit request, maar de data misschien niet...
            // We moeten hier zien te achterhalen voor welk request de data is en
            // dus op welke buffer die hoort...

            $this->buffer->chunk($chunk);

            if (Str::endsWith($chunk, '===stew-data-end===')) {

                $resolve = $this->resolve;
                $resolve($this->buffer->tunnelResponse()->getResponse());

                $this->buffer->clear();
                $this->release();
            }
        });

        $this->socket = $socket;
        $this->id = $id;
        $this->buffer = new Buffer();
    }

    /**
     * @return self
     */
    public function release()
    {
        $this->busy = false;

        return $this;
    }

    /**
     * @return self
     */
    public function occupy()
    {
        $this->busy = true;

        return $this;
    }

    /**
     * @param string $data
     *
     * @return \App\Tunnel\Client\Connection
     */
    public function write(string $data)
    {
        $this->getSocket()->write($data);

        return $this;
    }

    /**
     * @return bool
     */
    public function isBusy()
    {
        return $this->busy || ! $this->getSocket()->isWritable();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return \React\Socket\ConnectionInterface
     */
    public function getSocket(): ConnectionInterface
    {
        return $this->socket;
    }

    /**
     * @param callable $resolve
     * @param callable $reject
     *
     * @return self
     */
    public function callbacks(callable $resolve, callable $reject)
    {
        $this->resolve = $resolve;
        $this->reject = $reject;

        return $this;
    }
}
