<?php

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

final class ExamplesChildProcess implements ChildInterface
{
    public static function create(Messenger $messenger, LoopInterface $loop): void
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
        $messenger->registerRpc('overflow', function () {
            \ini_set('memory_limit', '20M');

            $string = '';
            while (true) {
                $string .= '0123456789';
            }
        });
    }

    public static function isPrime($n)
    {
        // Source: https://stackoverflow.com/a/39743570
        for ($i = $n >> 1; $i && $n % $i--; /* void */) {
            /* void */
        }

        return !$i && $n > 1;
    }
}
