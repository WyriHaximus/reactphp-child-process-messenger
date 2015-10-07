<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\FulfilledPromise;
use React\Stream\Stream;
use React\Stream\Util;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class Factory
{
    const INTERVAL = 0.1;
    const TIMEOUT = 13;

    public static function parent(
        Process $process,
        LoopInterface $loop,
        array $options = [],
        $interval = self::INTERVAL
    ) {
        $process->start($loop, $interval);

        return \WyriHaximus\React\tickingPromise($loop, $interval, [$process, 'isRunning'])->
            then(function () use ($process, $options) {
                $messenger = new Messenger($process->stdin, $process->stdout, $process->stderr, [
                    'read_err' => 'stderr',
                    'read' => 'stdout',
                    'write' => 'stdin',
                    'callForward' => function ($name, $arguments) use ($process) {
                        return call_user_func_array([$process, $name], $arguments);
                    },
                ] + $options);

                Util::forwardEvents($process, $messenger, [
                    'exit',
                ]);

                return \React\Promise\resolve($messenger);
            })
        ;
    }

    public static function child(LoopInterface $loop, array $options = [])
    {
        $messenger = new Messenger(new Stream(STDIN, $loop), new Stream(STDOUT, $loop), new Stream(STDERR, $loop), [
            'read' => 'stdin',
            'write_err' => 'stderr',
            'write' => 'stdout',
        ] + $options);

        $messenger->registerRpc('wyrihaximus.react.child-process.messenger.terminate', function (Payload $payload, Messenger $messenger) use ($loop) {
            $messenger->emit('terminate', [
                $messenger,
            ]);
            $loop->addTimer(1, [$loop, 'stop']);
            return new FulfilledPromise();
        });

        return $messenger;
    }
}
