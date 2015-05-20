<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use React\Promise\Deferred;

class Rpc implements \JsonSerializable, ActionableMessageInterface
{
    /**
     * @var string
     */
    protected $target;

    /**
     * @var Payload
     */
    protected $payload;

    /**
     * @var string
     */
    protected $uniqid;

    /**
     * @param string $target
     * @param Payload $payload
     * @param string $uniqid
     */
    public function __construct($target, Payload $payload, $uniqid = '')
    {
        $this->target = $target;
        $this->payload = $payload;
        $this->uniqid = $uniqid;
    }

    /**
     * @return Payload
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param $uniqid
     * @return static
     */
    public function setUniqid($uniqid)
    {
        return new static($this->target, $this->payload, $uniqid);
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'rpc',
            'uniqid' => $this->uniqid,
            'target' => $this->target,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param $bindTo
     * @param $source
     */
    public function handle($bindTo, $source)
    {
        $cb = function ($target, $payload, $uniqid) {
            if (!isset($this->rpcs[$target])) {
                $this->stderr->write((string) new Line(new RpcError($uniqid, new Payload([
                    'message' => 'Target doesn\'t exist',
                ]))));
                return;
            }

            $deferred = new Deferred();

            $deferred->promise()->then(function (array $payload) use ($uniqid) {
                $this->getProcess()->stdout->write((string) new Line(new RpcSuccess($uniqid, new Payload($payload))));
            }, function (array $payload) use ($uniqid) {
                $this->getProcess()->stdout->write((string) new Line(new RpcNotify($uniqid, new Payload($payload))));
            }, function (array $payload) use ($uniqid) {
                $this->getProcess()->stderr->write((string) new Line(new RpcError($uniqid, new Payload($payload))));
            });

            $this->rpcs[$target]($payload, $deferred);
        };
        $cb = $cb->bindTo($bindTo);
        $cb($this->target, $this->payload, $this->uniqid);
    }
}
