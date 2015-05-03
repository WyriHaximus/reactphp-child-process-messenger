<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

class Payload
{
    /**
     * @var array
     */
    protected $payload = [];

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
