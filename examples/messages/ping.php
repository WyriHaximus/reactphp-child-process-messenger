<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\Timer\Timer;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = EventLoopFactory::create();

$process = new Process('exec php ' . dirname(dirname(__DIR__)) . '/examples/messages/pong.php');

MessengerFactory::parent($process, $loop)->then(function (Messenger $messenger) use ($loop) {
    $messenger->on('message', function (Payload $payload) {
        echo $payload['time'], PHP_EOL;
    });

    $messenger->on('error', function ($e) {
        echo 'Error: ', var_export($e, true), PHP_EOL;
    });

    $i = 0;
    $loop->addPeriodicTimer(0.001, function (Timer $timer) use (&$i, $messenger) {
        if ($i >= 1300000) {
            $timer->cancel();
            $messenger->terminate();

            return;
        }

        $messenger->message(MessagesFactory::message([
            'i' => $i,
            'time' => time(),
        ]));

        $i++;
    });
});

$loop->run();
