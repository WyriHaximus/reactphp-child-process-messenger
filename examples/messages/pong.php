<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

$loop = EventLoopFactory::create();

$recipient = Factory::child($loop);
$recipient->on('message', function (Payload $payload, Messenger $messenger) {
    $messenger->message(MessagesFactory::message([
        'time' => (new DateTime('@' . $payload['time'] * $payload['i']))->format('c')
    ]));
});

$loop->run();
