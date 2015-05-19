<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\Timer\Timer;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = EventLoopFactory::create();

$messenger = new Messenger(new Process('exec php ' . dirname(dirname(__DIR__)) . '/examples/messages/pong.php'));

$messenger->on('message', function (Payload $payload) {
    echo $payload['time'], PHP_EOL;
});

$messenger->start($loop)->then(function (Messenger $messenger) use ($loop) {
    $i = 0;
    $loop->addPeriodicTimer(1, function (Timer $timer) use (&$i, $messenger) {
        if ($i >= 133) {
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
