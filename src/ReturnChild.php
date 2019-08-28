<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class ReturnChild implements ChildInterface
{
    /**
     * @var bool
     */
    protected $ran = false;

    /**
     * @param Messenger     $messenger
     * @param LoopInterface $loop
     */
    protected function __construct(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('return', function (Payload $payload) {
            return \React\Promise\resolve($payload->getPayload());
        });
        $messenger->on('message', function (Payload $payload) use ($messenger) {
            $messenger->message(MessagesFactory::message($payload->getPayload()));
        });
        $this->ran = true;
    }

    /**
     * @param Messenger     $messenger
     * @param LoopInterface $loop
     */
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        new static($messenger, $loop);
    }

    /**
     * @return bool
     */
    public function getRan()
    {
        return $this->ran;
    }
}
