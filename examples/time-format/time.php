<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use React\EventLoop\Timer\Timer;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = Factory::create();
$process = new Process('exec php ' . dirname(dirname(__DIR__)) . '/examples/time-format/format.php');

\WyriHaximus\React\ChildProcess\Messenger\Factory::parent($process, $loop)->then(function (Messenger $messenger) use ($loop) {
    $i = 0;

    $loop->addPeriodicTimer(1, function (Timer $timer) use ($messenger, &$i) {
        if ($i >= 13) {
            $messenger->terminate();
            $timer->cancel();
            return;
        }

        $messenger->rpc(MessageFactory::rpc('format', [
            'unixTime' => time(),
        ]))->then(function ($formattedTime) {
            echo $formattedTime, PHP_EOL;
        });
        $i++;
    });

});

$loop->run();
