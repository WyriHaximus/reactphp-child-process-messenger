<?php

require \dirname(\dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\TimerInterface;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = EventLoopFactory::create();

MessengerFactory::parentFromClass(\WyriHaximus\React\ChildProcess\Messenger\ReturnChild::class, $loop)->done(function (Messenger $messenger) use ($loop) {
    $messenger->on('message', function ($message) {
        echo 'Message: ', \var_export($message, true), PHP_EOL;
    });
    $messenger->on('error', function ($e) {
        echo 'Error: ', \var_export($e, true), PHP_EOL;
    });

    $i = 0;
    $loop->addPeriodicTimer(1, function (TimerInterface $timer) use (&$i, $messenger, $loop) {
        echo 'tick', PHP_EOL;
        if ($i >= 13) {
            $loop->cancelTimer($timer);
            $messenger->softTerminate();

            return;
        }

        $messenger->message(MessagesFactory::message([
            'i' => $i,
            'time' => \time(),
        ]));

        $i++;
    });
});

$loop->run();
