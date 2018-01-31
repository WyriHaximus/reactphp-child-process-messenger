<?php

use React\EventLoop\Factory as LoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\ArgvEncoder;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

foreach ([
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../../autoload.php',
] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

$arguments = array_pop($argv);
$loop = LoopFactory::create();
MessengerFactory::child($loop, ArgvEncoder::decode($arguments))->done(function (Messenger $messenger) use ($loop) {
    $messenger->registerRpc('hello', function (Payload $payload, Messenger $messenger) {
        sleep(1);
        return \React\Promise\resolve([
            'world' => 'hello world',
        ]);
    });
});
$loop->run();
