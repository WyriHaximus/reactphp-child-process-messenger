<?php

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class ExamplesChildProcess implements ChildInterface
{
    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        $messenger->registerRpc('error', function (Payload $payload) {
            throw new Exception('whoops');
        });

        $messenger->registerRpc('isPrime', function (Payload $payload) {
            return \React\Promise\resolve([
                'isPrime' => self::isPrime($payload['number']),
            ]);
        });
        $messenger->on('message', function (Payload $payload, Messenger $messenger) {
            $messenger->message(MessagesFactory::message([
                'time' => (new DateTime('@' . $payload['time'] * $payload['i']))->format('c'),
            ]));
        });
        $messenger->registerRpc('format', function (Payload $payload) {
            return \React\Promise\resolve([
                'formattedTime' => (new DateTime('@' . $payload['unixTime']))->format('c'),
            ]);
        });
    }

    private static function isPrime($n)
    {
        // Source: https://stackoverflow.com/a/39743570
        for ($i=$n>>1;$i&&$n%$i--;);

        return!$i&&$n>1;
    }
}
