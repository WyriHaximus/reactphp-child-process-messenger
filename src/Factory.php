<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\Server;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessengesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

final class Factory
{
    const INTERVAL = 0.1;
    const TIMEOUT = 13;
    const TERMINATE_TIMEOUT = 1;
    const PROCESS_REGISTER = 'wyrihaximus.react.child-process.messenger.child.register';

    /**
     * @param  string                              $process
     * @param  LoopInterface                       $loop
     * @param  array                               $options
     * @param  mixed                               $class
     * @return Promise\PromiseInterface<Overwatch>
     */
    public static function parent(
        $class,
        LoopInterface $loop,
        array $options = []
    ) {
        if (!is_subclass_of($class, 'WyriHaximus\React\ChildProcess\Messenger\ChildInterface')) {
            throw new \Exception('Given class doesn\'t implement ChildInterface');
        }

        return new Promise\Promise(function ($resolve, $reject) use ($class, $loop, $options) {
            $server = new Server('127.0.0.1:0', $loop);
            $options['address'] = $server->getAddress();

            $template = '%s';
            if (isset($options['cmdTemplate'])) {
                $template = $options['cmdTemplate'];
                unset($options['cmdTemplate']);
            }

            $phpBinary = \escapeshellarg(PHP_BINARY . (PHP_SAPI === 'phpdbg' ? ' -qrr --' : ''));
            $childProcessPath = \escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'child-process.php');
            $argvString = \escapeshellarg(ArgvEncoder::encode($options));
            $command = $phpBinary . ' ' . $childProcessPath;
            $process = new Process(
                sprintf(
                    $template,
                    $command . ' ' . $argvString
                )
            );

            $server->on('connection', function (ConnectionInterface $connection) use ($server, $resolve, $reject, $class, $options) {
                $server->pause();
                $messenger = new Messenger($connection, $options);
                $resolve($messenger->rpc(MessengesFactory::rpc(Factory::PROCESS_REGISTER, [
                    'className' => $class,
                ]))->then(function () use ($messenger) {
                    return Promise\resolve($messenger);
                }, $reject));
            });
            $server->on('error', function ($et) use ($reject) {
                $reject($et);
            });

            $process->start($loop);
        });
    }

    /**
     * @param  LoopInterface                       $loop
     * @param  array                               $options
     * @param  callable                            $termiteCallable
     * @return Promise\PromiseInterface<Messenger>
     */
    public static function child(LoopInterface $loop, array $options = [], callable $termiteCallable = null)
    {
        return (new Connector($loop))->connect($options['address'])->then(function (ConnectionInterface $connection) use ($options) {
            return new Messenger($connection, $options);
        })->then(function (Messenger $messenger) use ($loop, $termiteCallable) {
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
                function (Payload $payload, Messenger $messenger) use ($termiteCallable) {
                    $messenger->emit('terminate', [
                        $messenger,
                    ]);
                    $termiteCallable($payload, $messenger);

                    return Promise\resolve([]);
                }
            );

            return $messenger;
        });
    }
}
