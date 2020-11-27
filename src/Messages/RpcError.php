<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Closure;
use Exception;
use JsonSerializable;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

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

    public function handle(Messenger $bindTo, string $source): void
    {
        $cb = Closure::fromCallable(function ($payload, $uniqid): void {
            $this->getOutstandingCall($uniqid)->reject($payload); /** @phpstan-ignore-line  */
        });
        $cb = $cb->bindTo($bindTo);
        $cb($this->payload, $this->uniqid);
    }
}
