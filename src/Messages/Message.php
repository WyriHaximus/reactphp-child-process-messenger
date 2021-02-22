<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use JsonSerializable;

final class Message implements JsonSerializable, ActionableMessageInterface
{
    protected Payload $payload;

    public function __construct(Payload $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload(): Payload
    {
        return $this->payload;
    }

    /**
     * @return array<string, string|Payload>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'message',
            'payload' => $this->payload,
        ];
    }

    public function handle(object $bindTo, string $source): void
    {
        $cb = function (Payload $payload): void {
            /**
             * @psalm-suppress UndefinedMethod
             */
            $this->emit('message', [ /** @phpstan-ignore-line  */
                $payload,
                $this,
            ]);
        };
        $cb = $cb->bindTo($bindTo);
        /**
         * @psalm-suppress PossiblyInvalidFunctionCall
         */
        $cb($this->payload);
    }
}
