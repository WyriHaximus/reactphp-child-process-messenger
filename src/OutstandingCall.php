<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\Promise\Deferred;

class OutstandingCall
{
    /**
     * @var string
     */
    protected $uniqid;

    /**
     * @var Deferred
     */
    protected $deferred;

    /**
     * @param string $uniqid
     * @param callable $canceller
     */
    public function __construct($uniqid, callable $canceller = null)
    {
        if ($canceller !== null) {
            $canceller = \Closure::bind($canceller, $this, static::class);
        }

        $this->uniqid = $uniqid;
        $this->deferred = new Deferred($canceller);
    }
    /**
     * @return mixed
     */
    public function getUniqid()
    {
        return $this->uniqid;
    }

    /**
     * @return Deferred
     */
    public function getDeferred()
    {
        return $this->deferred;
    }
}
