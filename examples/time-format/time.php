<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\Timer\Timer;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = LoopFactory::create();
$process = new Process('exec php ' . dirname(dirname(__DIR__)) . '/examples/time-format/format.php');

MessengerFactory::parent($process, $loop)->then(function (Messenger $messenger) use ($loop) {
    $i = 0;

    $messenger->on('error', function ($e) {
        echo 'Error: ', var_export($e, true), PHP_EOL;
    });

    $loop->addPeriodicTimer(1, function (Timer $timer) use ($messenger, &$i) {
        if ($i >= 13) {
            $messenger->terminate();
            $timer->cancel();
            return;
        }

        $messenger->rpc(MessageFactory::rpc('format', [
            'unixTime' => time(),
        ]))->then(function ($formattedTime) {
            echo $formattedTime['formattedTime'], PHP_EOL;
        });
        $i++;
    });

});

$loop->run();
