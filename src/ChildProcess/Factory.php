<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\ChildProcess;

use React\EventLoop\Loop;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

final class Factory
{
    public static function boot(string $arguments): int
    {
        $exitCode = 0;

        $loop = Loop::get();
        MessengerFactory::child($loop, ArgvEncoder::decode($arguments))->then(static function (Messenger $messenger) use ($loop): void {
            Process::create($loop, $messenger);
        })->then(null, static function () use ($loop, &$exitCode): void {
            $loop->stop();
            $exitCode = 1;
        });

        $loop->run();

        return $exitCode;
    }
}
