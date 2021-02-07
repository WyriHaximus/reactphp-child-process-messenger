<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use JsonSerializable;

use function Safe\json_encode;

final class Line implements LineInterface
{
    protected JsonSerializable $payload;

    /**
     * @param array<mixed> $options
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(ActionableMessageInterface $payload, array $options)
    {
        $this->payload = $payload;
    }

    public function __toString(): string
    {
        return json_encode($this->payload) . LineInterface::EOL;
    }

    public function getPayload(): JsonSerializable
    {
        return $this->payload;
    }
}
