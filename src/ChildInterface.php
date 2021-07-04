<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\EventLoop\LoopInterface;

interface ChildInterface
{
    /**
     * @return void
     *
     * @psalm-suppress MissingReturnType
     */
    public static function create(Messenger $messenger, LoopInterface $loop); // phpcs:disabled
}
