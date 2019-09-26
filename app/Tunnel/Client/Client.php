<?php

namespace App\Tunnel\Client;

use React\Socket\ConnectionInterface;

class Client
{
    /**
     * @var \App\Tunnel\Client\ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var string
     */
    protected $id;

    /**
     * Client constructor.
     *
     * @param string $id
     */
    public function __construct(
        string $id
    ) {
        $this->connectionPool = new ConnectionPool();
        $this->id = $id;
    }

    /**
     * @param \React\Socket\ConnectionInterface $connection
     *
     * @return string
     */
    public function addConnection(ConnectionInterface $connection)
    {
        return $this->connectionPool->add($connection);
    }

    /**
     * @return \App\Tunnel\Client\Connection|null
     */
    public function nextConnection()
    {
        do {
            $connection = $this->connectionPool->next();

            sleep(1);
        } while (empty($connection));

        return $connection;
    }

    /**
     * @param string $host
     *
     * @return bool
     */
    public function ownsDomain(string $host)
    {
        return true; // yet to implement
    }
}
