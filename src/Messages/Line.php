<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

class Line implements LineInterface
{
    const EOL = PHP_EOL;

    /**
     * @var \JsonSerializable
     */
    protected $payload;

    /**
     * @param \JsonSerializable $payload
     */
    public function __construct(\JsonSerializable $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return \JsonSerializable
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->payload) . self::EOL;
    }
}
