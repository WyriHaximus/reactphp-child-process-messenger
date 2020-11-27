<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Stub;

use Evenement\EventEmitterTrait;
use React\Socket\ConnectionInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

final class ConnectionStub implements ConnectionInterface
{
    use EventEmitterTrait;

    private string $data = '';

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function pause(): void
    {
    }

    public function resume(): void
    {
    }

    /**
     * @param array<mixed> $options
     */
    public function pipe(WritableStreamInterface $dest, array $options = []): WritableStreamInterface
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    // phpcs:disabled
    public function write($data): bool
    {
        $this->data .= $data;

        return true;
    }

    // phpcs:disabled
    /** @phpstan-ignore-next-line */
    public function end($data = null): void
    {
    }

    public function close(): void
    {
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getRemoteAddress(): string
    {
        return '127.0.0.1';
    }

    public function getLocalAddress(): string
    {
        return '127.0.0.1';
    }
}
