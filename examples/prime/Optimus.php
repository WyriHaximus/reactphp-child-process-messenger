<?php

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class Optimus implements ChildInterface
{
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('isPrime', function (Payload $payload) {
            return \React\Promise\resolve([
                'isPrime' => self::isPrime($payload['number']),
            ]);
        });
    }

    private static function isPrime($n)
    {
        // Source: https://stackoverflow.com/a/39743570
        for($i=$n>>1;$i&&$n%$i--;);return!$i&&$n>1;
    }
}
