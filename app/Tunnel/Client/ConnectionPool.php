<?php

namespace App\Tunnel\Client;

use Illuminate\Support\Str;
use React\Socket\ConnectionInterface;

class ConnectionPool
{
    /**
     * @var \App\Tunnel\Client\Connection[]
     */
    protected $connections;

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

        $this->connections[$key] = new Connection($key, $connection);

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
