<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = Factory::create();

MessengerFactory::parent(new Process('exec php ' . __DIR__ . DIRECTORY_SEPARATOR . 'child.php foo bar'), $loop)->then(function (Messenger $messenger) {
    return $messenger->rpc(
        MessageFactory::rpc('hello')
    )->always(function () use ($messenger) {
        $messenger->softTerminate();
    });
})->done(function (Payload $result) {
    var_export($result);
}, function ($et) {
    echo (string)$et;
});

$loop->run();
