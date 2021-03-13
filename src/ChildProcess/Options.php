<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\ChildProcess;

final class Options
{
    private string $random;
    private string $address;
    private int $connectTimeout;

    public function __construct(string $random, string $address, int $connectTimeout)
    {
        $this->random         = $random;
        $this->address        = $address;
        $this->connectTimeout = $connectTimeout;
    }

    public function random(): string
    {
        return $this->random;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function connectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * @return array<string|int>
     */
    public function toArray(): array
    {
        return [
            $this->random,
            $this->address,
            $this->connectTimeout,
        ];
    }
}
