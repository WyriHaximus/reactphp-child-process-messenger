<?php

require \dirname(\dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\TimerInterface;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Messenger\ReturnChild;

$options = [
    'lineClass' => 'WyriHaximus\React\ChildProcess\Messenger\Messages\SecureLine',
    'lineOptions' => [
        'key' => 'abc123',
    ],
];

$loop = EventLoopFactory::create();

MessengerFactory::parentFromClass(ReturnChild::class, $loop, $options)->then(function (Messenger $messenger) use ($loop) {
    $messenger->on('error', function ($e) {
        echo 'Error: ', \var_export($e, true), PHP_EOL;
    });

    $i = 0;
    $loop->addPeriodicTimer(1, function (TimerInterface $timer) use (&$i, $messenger, $loop) {
        if ($i >= 13) {
            $loop->cancelTimer($timer);
            $messenger->softTerminate();

            return;
        }

        $messenger->rpc(MessagesFactory::rpc('return', [
            'i' => $i,
            'time' => \time(),
        ]))->then(function (Payload $payload) {
            echo $payload['time'], PHP_EOL;
        });

        $i++;
    });
});

$loop->run();
