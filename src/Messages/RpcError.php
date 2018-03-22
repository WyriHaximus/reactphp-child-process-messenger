<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Exception;
use Throwable;

class RpcError implements \JsonSerializable, ActionableMessageInterface
{
    /**
     * @var string
     */
    protected $uniqid;

    /**
     * @var Exception|Throwable
     */
    protected $payload;

    /**
     * @param string              $uniqid
     * @param Exception|Throwable $payload
     */
    public function __construct($uniqid, $payload)
    {
        $this->uniqid = $uniqid;
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
            'type' => 'rpc_error',
            'uniqid' => $this->uniqid,
            'payload' => LineEncoder::encode($this->payload),
        ];
    }

    /**
     * @param $bindTo
     * @param $source
     */
    public function handle($bindTo, $source)
    {
        $cb = function ($payload, $uniqid) {
            $this->getOutstandingCall($uniqid)->reject($payload);
        };
        $cb = $cb->bindTo($bindTo);
        $cb($this->payload, $this->uniqid);
    }
}
