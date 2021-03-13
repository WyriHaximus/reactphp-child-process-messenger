<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Closure;
use JsonSerializable;
use WyriHaximus\React\ChildProcess\Messenger\MessengerInterface;

final class RpcSuccess implements JsonSerializable, ActionableMessageInterface
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
            'type' => 'rpc_success',
            'uniqid' => $this->uniqid,
            'payload' => $this->payload,
        ];
    }

    public function handle(MessengerInterface $bindTo, string $source): void
    {
        $cb = Closure::fromCallable(function (Payload $payload, string $uniqid): void {
            /**
             * @psalm-suppress UndefinedMethod
             */
            $this->getOutstandingCall($uniqid)->resolve($payload); /** @phpstan-ignore-line  */
        });
        $cb = $cb->bindTo($bindTo);
        /**
         * @psalm-suppress PossiblyInvalidFunctionCall
         */
        $cb($this->payload, $this->uniqid);
    }
}
