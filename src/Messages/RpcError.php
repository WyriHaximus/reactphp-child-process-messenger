<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Closure;
use Exception;
use JsonSerializable;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\MessengerInterface;

final class RpcError implements JsonSerializable, ActionableMessageInterface
{
    protected string $uniqid;

    protected Throwable $payload;

    public function __construct(string $uniqid, Throwable $payload)
    {
        $this->uniqid  = $uniqid;
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
     * @return array<string,string|mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'rpc_error',
            'uniqid' => $this->uniqid,
            'payload' => LineEncoder::encode($this->payload),
        ];
    }

    public function handle(MessengerInterface $bindTo, string $source): void
    {
        $cb = Closure::fromCallable(function (Throwable $payload, string $uniqid): void {
            /**
             * @psalm-suppress UndefinedMethod
             */
            $this->getOutstandingCall($uniqid)->reject($payload); /** @phpstan-ignore-line  */
        });
        $cb = $cb->bindTo($bindTo);
        /**
         * @psalm-suppress PossiblyInvalidFunctionCall
         */
        $cb($this->payload, $this->uniqid);
    }
}
