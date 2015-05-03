<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

class Call
{
    /**
     * @var string
     */
    protected $target;

    /**
     * @var Payload
     */
    protected $message;

    public function __construct($target, Payload $message)
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
     * @return Payload
     */
    public function getMessage()
    {
        return $this->message;
    }
}
