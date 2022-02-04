<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\EventLoop\LoopInterface;

interface ChildInterface
{
    public static function create(Messenger $messenger, LoopInterface $loop): mixed;
}
