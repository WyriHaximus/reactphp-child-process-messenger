<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use ArrayAccess;
use JsonSerializable;
// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use ReturnTypeWillChange;

/**
 * @phpstan-ignore-next-line
 */
final class Payload implements JsonSerializable, ArrayAccess
{
    /** @var array<mixed> */
    protected array $payload = [];

    /**
     * @param array<mixed> $payload
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(array $payload = []) /** @phpstan-ignore-line  */
    {
        $this->payload = $payload;
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->payload;
    }

    /**
     * @param mixed|null $offset
     * @param mixed      $value
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->payload[] = $value;
        } else {
            $this->payload[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        /** @phpstan-ignore-next-line */
        return isset($this->payload[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->payload[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->payload[$offset] ?? null;
    }
}
