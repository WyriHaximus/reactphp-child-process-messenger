<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Closure;
use JsonSerializable;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

final class RpcNotify implements JsonSerializable, ActionableMessageInterface
{
    protected string $uniqid;

    protected Payload $payload;

    public function __construct(string $uniqid, Payload $payload)
    {
        $this->uniqid  = $uniqid;
        $this->payload = $payload;
    }

    public function getPayload(): Payload
    {
        return $this->payload;
    }

    /**
     * @return array<string,string|mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'rpc_notify',
            'uniqid' => $this->uniqid,
            'payload' => $this->payload,
        ];
    }

    public function handle(Messenger $bindTo, string $source): void
    {
        $cb = Closure::fromCallable(function ($payload, $uniqid): void {
            $this->getOutstandingCall($uniqid)->progress($payload); /** @phpstan-ignore-line  */
        });
        $cb = $cb->bindTo($bindTo);
        $cb($this->payload, $this->uniqid);
    }
}
