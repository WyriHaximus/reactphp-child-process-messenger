<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;

class Invoke
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Payload
     */
    protected $payload;

    /**
     * @var Deferred
     */
    protected $deferred;

    public function __construct(LoopInterface $loop, Payload $payload)
    {
        $this->loop = $loop;
        $this->payload = $payload;

        $this->deferred = new Deferred();
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload->getPayload();
    }

    /**
     * @return Deferred
     */
    public function getDeferred()
    {
        return $this->deferred;
    }

    /**
     * @return \React\Promise\PromiseInterface
     */
    public function getPromise()
    {
        return $this->deferred->promise();
    }
}
