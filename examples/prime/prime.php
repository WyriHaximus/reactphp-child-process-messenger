<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = Factory::create();

$prime = isset($argv[1]) ? (int)$argv[1] : mt_rand(1, 1337);

echo 'Checking if ', $prime, ' is a prime or not', PHP_EOL;

MessengerFactory::parentFromClass(\Optimus::class, $loop)->then(function (Messenger $messenger) use ($prime) {
    return $messenger->rpc(
        MessageFactory::rpc('isPrime', ['number' => $prime])
    )->always(function () use ($messenger) {
        $messenger->softTerminate();
    });
})->done(function (Payload $result) {
    if ($result['isPrime']) {
        echo 'Prime', PHP_EOL;
        return;
    }

    echo 'Not a prime', PHP_EOL;
});

$loop->run();