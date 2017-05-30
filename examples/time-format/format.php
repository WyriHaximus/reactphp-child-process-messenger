<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

$loop = Factory::create();

$recipient = \WyriHaximus\React\ChildProcess\Messenger\Factory::child($loop);
$recipient->registerRpc('format', function (Payload $payload) use ($loop) {
    return \React\Promise\resolve([
        'formattedTime' => (new DateTime('@' . $payload['unixTime']))->format('c'),
    ]);
});

$loop->run();
