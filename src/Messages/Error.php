<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use JsonSerializable;
use Throwable;

final class Error implements JsonSerializable, ActionableMessageInterface
{
    protected Throwable $payload;

    public function __construct(Throwable $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload(): Throwable
    {
        return $this->payload;
    }

    /**
     * @return array<string,string|Throwable>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'error',
            'payload' => $this->payload,
        ];
    }

    public function handle(object $bindTo, string $source): void
    {
        $cb = function ($payload): void {
            $this->emit('error', [ /** @phpstan-ignore-line  */
                $payload,
                $this,
            ]);
        };
        $cb = $cb->bindTo($bindTo);
        $cb($this->payload);
    }
}
