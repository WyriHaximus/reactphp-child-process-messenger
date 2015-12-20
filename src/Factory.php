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
    const TERMINATE_TIMEOUT = 1;

    /**
     * @param Process $process
     * @param LoopInterface $loop
     * @param array $options
     * @param float $interval
     * @return \React\Promise\PromiseInterface
     */
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

    /**
     * @param LoopInterface $loop
     * @param array $options
     * @param null $termiteCallable
     * @return Messenger
     */
    public static function child(LoopInterface $loop, array $options = [], $termiteCallable = null)
    {
        $messenger = new Messenger(new Stream(STDIN, $loop), new Stream(STDOUT, $loop), new Stream(STDERR, $loop), [
            'read' => 'stdin',
            'write_err' => 'stderr',
            'write' => 'stdout',
        ] + $options);

        if ($termiteCallable === null) {
            $termiteCallable = function () use ($loop) {
                $loop->addTimer(
                    self::TERMINATE_TIMEOUT,
                    [
                        $loop,
                        'stop',
                    ]
                );
            };
        }

        $messenger->registerRpc(
            Messenger::TERMINATE_RPC,
            function (Payload $payload, Messenger $messenger) use ($loop, $termiteCallable) {
                $messenger->emit('terminate', [
                    $messenger,
                ]);
                $termiteCallable($payload, $messenger);
                return new FulfilledPromise();
            }
        );

        return $messenger;
    }
}
