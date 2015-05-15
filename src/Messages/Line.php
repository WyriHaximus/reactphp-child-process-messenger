<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

class Line
{
    const EOL = PHP_EOL;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->payload) . self::EOL;
    }
}
