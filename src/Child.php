<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\Socket\ConnectionInterface;
use React\Socket\Server;

final class Child
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $server->on('connection', function (ConnectionInterface $connection) use ($server) {
            $server->pause();
            $this->handleConnection($connection);
        });
    }

    private function handleConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }
}