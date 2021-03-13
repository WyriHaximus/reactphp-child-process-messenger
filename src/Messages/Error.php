<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use JsonSerializable;
use Throwable;

use function WyriHaximus\throwable_encode;

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
     * @return array<string,string|array<mixed>>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'error',
            'payload' => throwable_encode($this->payload),
        ];
    }

    public function handle(object $bindTo, string $source): void
    {
        $cb = function (Throwable $payload): void {
            /**
             * @psalm-suppress UndefinedMethod
             */
            $this->emit('error', [ /** @phpstan-ignore-line  */
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
