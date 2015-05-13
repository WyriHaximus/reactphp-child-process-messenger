<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\EventLoop\LoopInterface;

trait LoopAwareTrait
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
