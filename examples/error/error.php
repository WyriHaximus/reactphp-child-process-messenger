<?php

require \dirname(\dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = Factory::create();

MessengerFactory::parentFromClass('\ExamplesChildProcess', $loop)->then(function (Messenger $messenger) {
    return $messenger->rpc(
        MessageFactory::rpc('error')
    )->always(function () use ($messenger) {
        $messenger->softTerminate();
    });
})->done(function (Payload $result) {
    throw new Exception('Should never reach this!');
}, function ($et) {
    echo (string)$et;
});

$loop->run();
