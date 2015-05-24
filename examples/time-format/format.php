<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

$loop = Factory::create();

$recipient = \WyriHaximus\React\ChildProcess\Messenger\Factory::child($loop);
$recipient->registerRpc('format', function (Payload $payload, Deferred $deferred) use ($loop) {
    $deferred->resolve([
        'formattedTime' => (new DateTime('@' . $payload['unixTime']))->format('c'),
    ]);
});

$loop->run();
