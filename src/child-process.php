<?php

declare(strict_types=1);

use React\EventLoop\Factory as LoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\ArgvEncoder;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Messenger\Process;

foreach (
    [
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../../../autoload.php',
    ] as $file
) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

$arguments = '';
if (isset($argv[1])) { /** @phpstan-ignore-line */
    $arguments = $argv[1];
}

$loop = LoopFactory::create();
MessengerFactory::child($loop, ArgvEncoder::decode($arguments))->done(static function (Messenger $messenger) use ($loop): void {
 /** @phpstan-ignore-line */
    Process::create($loop, $messenger);
}, static function () use ($loop): void {
    $loop->stop();
});
$loop->run();
