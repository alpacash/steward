<?php

namespace App\Tunnel\Client;

use App\Tunnel\TunnelRequest;
use React\Socket\ConnectionInterface;

class ClientPool
{
    /**
     * @var \App\Tunnel\Client\Client[]
     */
    protected $clients;

    /**
     * @param \React\Socket\ConnectionInterface $connection
     */
    public function addConnection(ConnectionInterface $connection)
    {
        $id = md5(current(explode(':', $connection->getRemoteAddress())));

        if (!isset($this->clients[$id])) {
            $this->clients[$id] = new Client($id);
        }

        $this->clients[$id]->addConnection($connection);
    }

    /**
     * @param \App\Tunnel\TunnelRequest $request
     *
     * @return \App\Tunnel\Client\Client|null
     */
    public function clientForRequest(TunnelRequest $request = null)
    {
        foreach ($this->clients ?? [] as $client) {
            if (empty($request) || $client->ownsDomain($request->getRequest()->getUri()->getHost())) {
                return $client;
            }
        }

        return null;
    }

    /**
     * @param \App\Tunnel\TunnelRequest $request
     *
     * @return \App\Tunnel\Client\Connection|null
     */
    public function nextConnectionForRequest(
        TunnelRequest $request = null
    ) {
        if ($client = $this->clientForRequest($request)) {
            $connection = $client->nextConnection();

            return $connection;
        }

        return null;
    }
}
