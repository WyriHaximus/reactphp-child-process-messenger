<?php

use React\EventLoop\Factory as LoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Process;

require __DIR__ . '/../vendor/autoload.php';

$loop = LoopFactory::create();
$messenger = MessengerFactory::child($loop);

Process::create($loop, $messenger);
$loop->run();
