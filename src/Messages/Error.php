<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Exception;
use Throwable;

class Error implements \JsonSerializable, ActionableMessageInterface
{
    /**
     * @var Exception|Throwable
     */
    protected $payload;

    /**
     * @param Exception|Throwable $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return Exception|Throwable
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'error',
            'payload' => $this->payload,
        ];
    }

    /**
     * @param $bindTo
     * @param $source
     */
    public function handle($bindTo, $source)
    {
        $cb = function ($payload) {
            $this->emit('error', [
                $payload,
                $this,
            ]);
        };
        $cb = $cb->bindTo($bindTo);
        $cb($this->payload);
    }
}
