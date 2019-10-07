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
    protected $busySince = false;

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
     * @var string
     */
    protected $timeout;

    /**
     * Connection constructor.
     *
     * @param string              $id
     * @param ConnectionInterface $socket
     * @param int                 $timeout
     */
    public function __construct(
        string $id,
        ConnectionInterface $socket,
        int $timeout = 60
    ) {
        $this->cli = new CLImate();

        $socket->on('data', function ($chunk) {

            $this->buffer->chunk($chunk);

            if (Str::endsWith($chunk, '===stew-data-end===')) {

                $this->resolve();
            }
        });

        $this->socket = $socket;
        $this->id = $id;
        $this->buffer = new Buffer();
        $this->timeout = $timeout;
    }

    /**
     * @return self
     */
    public function release()
    {
        $this->busySince = false;

        $this->buffer->clear();

        $this->cli->comment("Connection {$this->getId()} was released...");

        return $this;
    }

    /**
     * @return self
     */
    public function occupy()
    {
        $this->busySince = time();

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
        return $this->busySince !== false
             || ! $this->getSocket()->isWritable();
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

    /**
     * @return bool
     */
    public function timedOut()
    {
        return $this->busySince !== false
            && time() - $this->busySince > $this->timeout;
    }

    /**
     * @return self
     */
    protected function resolve()
    {
        if (is_callable($this->resolve)) {
            call_user_func_array($this->resolve, [$this->buffer->read()]);
        }

        $this->release();

        return $this;
    }

    /**
     * @return self
     */
    protected function reject()
    {
        if (is_callable($this->reject)) {
            call_user_func_array($this->reject, [$this->buffer->read()]);
        }

        return $this;
    }
}
