<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Recipient;

$loop = Factory::create();

$recipient = new Recipient($loop);
$recipient->registerRpc('format', function (Invoke $invoke) use ($loop) {
    $invoke->getDeferred()->resolve((new DateTime('@' . $invoke->getPayload()['unixTime']))->format('c'));
});

$loop->run();
