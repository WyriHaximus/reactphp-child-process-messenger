<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

class RpcCall
{
    /**
     * @var string
     */
    protected $target;

    /**
     * @var array
     */
    protected $message = [];

    public function __construct($target, array $message = [])
    {
        $this->target = $target;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return array
     */
    public function getMessage()
    {
        return $this->message;
    }
}
