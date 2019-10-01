<?php

namespace App\Tunnel\Client;

use Illuminate\Support\Str;
use League\CLImate\CLImate;
use React\Socket\ConnectionInterface;

class ConnectionPool
{
    /**
     * @var \App\Tunnel\Client\Connection[]
     */
    protected $connections;

    /**
     * @var \League\CLImate\CLImate
     */
    protected $cli;

    /**
     * ConnectionPool constructor.
     */
    public function __construct()
    {
        $this->cli = new CLImate();
    }

    /**
     * @param \React\Socket\ConnectionInterface $connection
     *
     * @return string
     */
    public function add(ConnectionInterface $connection)
    {
        do {
            $key = Str::random(12);
        } while (!empty($this->connections[$key]));

        // This response's destination could be anything we asked for,
        // thus we have to find out what it is for.
        $connection->on('end', function() use ($key) {
            $this->release($key);
        });

        $connection->on('close', function() use ($key) {
            $this->close($key);
        });

        $connection->on('error', function(\Exception $e) use ($key) {
            $this->cli->error($e->getMessage());

            unset($this->connections[$key]);
        });

        $this->connections[$key] = new Connection($key, $connection);
        $this->cli->comment("Adding new connection {$key}");

        return $this->connections[$key];
    }

    /**
     * @param string $id
     *
     * @return self
     */
    public function close(string $id)
    {
        $this->connections[$id]->getSocket()->close();
        unset($this->connections[$id]);

        return $this;
    }

    /**
     * @param string $id
     *
     * @return self
     */
    public function release(string $id)
    {
        $this->connections[$id]->release();

        return $this;
    }

    /**
     * @return \App\Tunnel\Client\Connection|null
     */
    public function next()
    {
        foreach ($this->connections as $connection) {
            if (!$connection->isBusy()) {
                $connection->occupy();

                return $connection;
            }
        }

        return null;
    }
}
