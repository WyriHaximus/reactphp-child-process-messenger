<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Closure;
use Exception;
use JsonSerializable;
use Throwable;

final class Rpc implements JsonSerializable, ActionableMessageInterface
{
    protected string $target;

    protected Payload $payload;

    protected string $uniqid;

    /** @phpstan-ignore-next-line  */
    public function __construct(string $target, Payload $payload, string $uniqid = '') /** @phpstan-ignore-line  */
    {
        $this->target  = $target;
        $this->payload = $payload;
        $this->uniqid  = $uniqid;
    }

    public function getPayload(): Payload
    {
        return $this->payload;
    }

    public function setUniqid(string $uniqid): self
    {
        return new self($this->target, $this->payload, $uniqid);
    }

    /**
     * @return array<string,string|mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'rpc',
            'uniqid' => $this->uniqid,
            'target' => $this->target,
            'payload' => $this->payload,
        ];
    }

    public function handle(object $bindTo, string $source): void
    {
        $cb = Closure::fromCallable(function (string $target, Payload $payload, string $uniqid): void {
            /**
             * @psalm-suppress UndefinedMethod
             */
            if (! $this->hasRpc($target)) { /** @phpstan-ignore-line  */
                $this->write($this->createLine(Factory::rpcError($uniqid, new Exception(sprintf('Rpc target <%s> doesn\'t exist',$target))))); /** @phpstan-ignore-line  */

                return;
            }

            /**
             * @psalm-suppress UndefinedMethod
             */
            $this->callRpc($target, $payload)->done( /** @phpstan-ignore-line  */
                function (array $payload) use ($uniqid): void {
                    /**
                     * @psalm-suppress UndefinedMethod
                     */
                    $this->write($this->createLine(Factory::rpcSuccess($uniqid, $payload))); /** @phpstan-ignore-line  */
                },
                function (Throwable $error) use ($uniqid): void {
                    /**
                     * @psalm-suppress UndefinedMethod
                     */
                    $this->write($this->createLine(Factory::rpcError($uniqid, $error))); /** @phpstan-ignore-line  */
                }
            );
        });
        $cb = $cb->bindTo($bindTo);
        /**
         * @psalm-suppress PossiblyInvalidFunctionCall
         */
        $cb($this->target, $this->payload, $this->uniqid);
    }
}
