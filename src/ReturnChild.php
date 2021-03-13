<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

use function React\Promise\resolve;

final class ReturnChild implements ChildInterface
{
    /** @phpstan-ignore-next-line */
    private function __construct(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('return', static function (Payload $payload): PromiseInterface {
            return resolve($payload->getPayload());
        });
        $messenger->on('message', static function (Payload $payload) use ($messenger): void {
            $messenger->message(MessagesFactory::message($payload->getPayload()));
        });
    }

    public static function create(Messenger $messenger, LoopInterface $loop): void
    {
        new static($messenger, $loop);
    }
}
