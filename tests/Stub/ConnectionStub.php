<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Stub;

use Evenement\EventEmitter;
use React\Socket\ConnectionInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

class ConnectionStub extends EventEmitter implements ConnectionInterface
{
    private $data = '';

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }

    public function pause()
    {
    }

    public function resume()
    {
    }

    public function pipe(WritableStreamInterface $dest, array $options = [])
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    public function write($data)
    {
        $this->data .= $data;

        return true;
    }

    public function end($data = null)
    {
    }

    public function close()
    {
    }

    public function getData()
    {
        return $this->data;
    }

    public function getRemoteAddress()
    {
        return '127.0.0.1';
    }

    public function getLocalAddress()
    {
        return '127.0.0.1';
    }
}
