<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use React\ChildProcess\Process;
use React\EventLoop\Factory;
use React\EventLoop\Timer\Timer;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

$loop = Factory::create();
$messenger = new Messenger(new Process('exec php ' . dirname(dirname(__DIR__)) . '/examples/time-format/format.php'));

$messenger->start($loop)->then(function ($messenger) use ($loop) {
    $i = 0;

    $loop->addPeriodicTimer(1, function (Timer $timer) use ($messenger, &$i) {
        if ($i >= 13) {
            $messenger->terminate();
            $timer->cancel();
            return;
        }

        $messenger->rpc(new Call('format', new Payload([
        'unixTime' => time(),
        ])))->then(function ($formattedTime) {
            echo $formattedTime, PHP_EOL;
        });
        $i++;
    });

});

$loop->run();
