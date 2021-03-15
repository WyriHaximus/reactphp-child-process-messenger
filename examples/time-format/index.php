<?php

require \dirname(\dirname(__DIR__)) . '/vendor/autoload.php';

use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\TimerInterface;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = LoopFactory::create();

MessengerFactory::parentFromClass(ExamplesChildProcess::class, $loop)->done(function (Messenger $messenger) use ($loop) {
    $i = 0;

    $messenger->on('error', function ($e) {
        echo 'Error: ', \var_export($e, true), PHP_EOL;
    });

    $loop->addPeriodicTimer(1, function (TimerInterface $timer) use ($messenger, &$i, $loop) {
        if ($i >= 13) {
            $messenger->softTerminate();
            $loop->cancelTimer($timer);

            return;
        }

        $messenger->rpc(MessageFactory::rpc('format', [
            'unixTime' => \time(),
        ]))->then(function ($formattedTime) {
            echo $formattedTime['formattedTime'], PHP_EOL;
        });
        $i++;
    });
});

$loop->run();
