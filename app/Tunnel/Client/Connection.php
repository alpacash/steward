<?php

namespace App\Tunnel\Client;

use App\Tunnel\Buffer;
use Illuminate\Support\Str;
use League\CLImate\CLImate;
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
     * @var \App\Tunnel\Buffer
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
     * @var \League\CLImate\CLImate
     */
    protected $cli;

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
        $this->cli = new CLImate();

        $socket->on('data', function ($chunk) {

            $this->buffer->chunk($chunk);

            if (Str::endsWith($chunk, '===stew-data-end===')) {

                if (is_callable($this->resolve)) {
                    call_user_func_array($this->resolve, [$this->buffer->read()]);
                }

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

        $this->cli->comment("Connection {$this->getId()} was released...");

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
     *
     * @return self
     */
    public function resolves(callable $resolve)
    {
        $this->resolve = $resolve;

        return $this;
    }

    /**
     * @param callable $reject
     *
     * @return self
     */
    public function rejects(callable $reject)
    {
        $this->reject = $reject;

        return $this;
    }
}
