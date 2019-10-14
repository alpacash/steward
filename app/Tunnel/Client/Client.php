<?php

namespace App\Tunnel\Client;

use League\CLImate\CLImate;
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
     * @var \League\CLImate\CLImate
     */
    protected $cli;

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
        $this->cli = new CLImate();
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

            if (empty($connection)) {
                $this->cli->yellow("Warning: running out of connections with {$this->id}");
                sleep(1);

                $this->connectionPool->prune();
            }
        } while (empty($connection));

        $this->cli->green("Going through with connection {$connection->getId()}");

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
