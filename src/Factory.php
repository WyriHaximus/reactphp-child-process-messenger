<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\Server;
use WyriHaximus\FileDescriptors\Factory as FileDescriptorsFactory;
use WyriHaximus\FileDescriptors\ListerInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessengesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

final class Factory
{
    const INTERVAL = 0.1;
    const TIMEOUT = 13;
    const TERMINATE_TIMEOUT = 1;
    const PROCESS_REGISTER = 'wyrihaximus.react.child-process.messenger.child.register';
    const DEFAULT_CONNECT_TIMEOUT = 15;

    public static function parent(
        Process $process,
        LoopInterface $loop,
        array $options = []
    ) {
        return new Promise\Promise(function ($resolve, $reject) use ($process, $loop, $options) {
            $server = new Server('127.0.0.1:0', $loop);

            $options['random'] = \bin2hex(\random_bytes(32));
            $options['address'] = $server->getAddress();
            $argvString = \escapeshellarg(ArgvEncoder::encode($options));
            $process = new Process($process->getCommand() . ' ' . $argvString);

            self::startParent($process, $server, $loop, $options)->done($resolve, $reject);
        });
    }

    /**
     * @param  string                              $class
     * @param  LoopInterface                       $loop
     * @param  array                               $options
     * @return Promise\PromiseInterface<Messenger>
     */
    public static function parentFromClass(
        $class,
        LoopInterface $loop,
        array $options = []
    ) {
        if (!\is_subclass_of($class, 'WyriHaximus\React\ChildProcess\Messenger\ChildInterface')) {
            throw new \Exception('Given class doesn\'t implement ChildInterface');
        }

        return new Promise\Promise(function ($resolve, $reject) use ($class, $loop, $options) {
            $server = new Server('127.0.0.1:0', $loop);
            $options['random'] = \bin2hex(\random_bytes(32));
            $options['address'] = $server->getAddress();

            $template = '%s';
            if (isset($options['cmdTemplate'])) {
                $template = $options['cmdTemplate'];
                unset($options['cmdTemplate']);
            }

            $fds = [];
            if (StaticConfig::shouldListFileDescriptors() && \DIRECTORY_SEPARATOR !== '\\') {
                if (isset($options['fileDescriptorLister']) && $options['fileDescriptorLister'] instanceof ListerInterface) {
                    /** @var ListerInterface $fileDescriptorLister */
                    $fileDescriptorLister = $options['fileDescriptorLister'];
                    unset($options['fileDescriptorLister']);
                }

                if (!isset($fileDescriptorLister)) {
                    /** @var ListerInterface $fileDescriptorLister */
                    $fileDescriptorLister = FileDescriptorsFactory::create();
                }

                foreach (self::listFileDescriptors(($fileDescriptorLister)) as $id) {
                    $fds[(int)$id] = ['file', '/dev/null', 'r'];
                }
            }

            $phpBinary = \escapeshellarg(PHP_BINARY . (PHP_SAPI === 'phpdbg' ? ' -qrr --' : ''));
            $childProcessPath = \escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'child-process.php');
            $argvString = \escapeshellarg(ArgvEncoder::encode($options));
            $command = $phpBinary . ' ' . $childProcessPath;

            $process = new Process(
                \sprintf(
                    $template,
                    $command . ' ' . $argvString
                ),
                null,
                null,
                $fds
            );

            $connectTimeout = isset($options['connect-timeout']) ? $options['connect-timeout'] : self::DEFAULT_CONNECT_TIMEOUT;
            \WyriHaximus\React\futurePromise($loop)->then(function () use ($process, $server, $loop, $options, $connectTimeout) {
                return Promise\Timer\timeout(self::startParent($process, $server, $loop, $options), $connectTimeout, $loop);
            })->then(function (Messenger $messenger) use ($class) {
                return $messenger->rpc(MessengesFactory::rpc(Factory::PROCESS_REGISTER, [
                    'className' => $class,
                ]))->then(function ($p) use ($messenger) {
                    return Promise\resolve($messenger);
                });
            })->done($resolve, $reject);
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
        $connectTimeout = isset($options['connect-timeout']) ? $options['connect-timeout'] : self::DEFAULT_CONNECT_TIMEOUT;

        return (new Connector($loop, ['timeout' => $connectTimeout]))->connect($options['address'])->then(function (ConnectionInterface $connection) use ($options, $loop, $connectTimeout) {
            return new Promise\Promise(function ($resolve, $reject) use ($connection, $options, $loop, $connectTimeout) {
                Promise\Timer\timeout(Promise\Stream\first($connection), $connectTimeout, $loop)->then(function ($chunk) use ($resolve, $reject, $connection, $options, $loop) {
                    list($confirmation) = \explode(PHP_EOL, $chunk);
                    if ($confirmation === 'syn') {
                        $connection->write('ack' . PHP_EOL);
                        $resolve(new Messenger($connection, $options));
                        $connection->on('close', [$loop, 'stop']);
                        $connection->on('error', [$loop, 'stop']);
                        return;
                    }

                    $reject(new \RuntimeException('Handshake SYN failed'));
                }, $reject);
                $connection->write(\hash_hmac('sha512', $options['address'], $options['random']) . PHP_EOL);
            });
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

    private static function startParent(
        Process $process,
        Server $server,
        LoopInterface $loop,
        array $options
    ) {
        return (new Promise\Promise(function ($resolve, $reject) use ($process, $server, $loop, $options) {
            $server->on(
                'connection',
                function (ConnectionInterface $connection) use ($server, $resolve, $reject, $options, $loop) {
                    Promise\Stream\first($connection)->then(function ($chunk) use ($options, $connection, $resolve) {
                        list($confirmation) = \explode(PHP_EOL, $chunk);
                        if ($confirmation === \hash_hmac('sha512', $options['address'], $options['random'])) {
                            $connection->write('syn' . PHP_EOL);

                            return Promise\Stream\first($connection);
                        }
                    })->then(function ($chunk) use ($options, $connection) {
                        list($confirmation) = \explode(PHP_EOL, $chunk);
                        if ($confirmation === 'ack') {
                            return Promise\resolve(new Messenger($connection, $options));
                        }

                        return Promise\reject(new \RuntimeException('Handshake failed'));
                    })->always(function () use ($server) {
                        $server->close();
                    })->done($resolve, $reject);
                }
            );
            $server->on('error', function ($et) use ($reject) {
                $reject($et);
            });

            $process->start($loop);
        }, function () use ($server, $process) {
            $server->close();
            $process->terminate();
        }))->then(function (Messenger $messenger) use ($loop, $process) {
            $loop->addPeriodicTimer(self::INTERVAL, function ($timer) use ($messenger, $loop, $process) {
                if (!$process->isRunning()) {
                    $loop->cancelTimer($timer);

                    $exitCode = $process->getExitCode();
                    if ($exitCode === 0) {
                        return;
                    }

                    $messenger->crashed($exitCode);
                }
            });

            return $messenger;
        });
    }

    private static function listFileDescriptors(ListerInterface $fileDescriptorLister)
    {
        if (\method_exists($fileDescriptorLister, 'list')) {
            return $fileDescriptorLister->list();
        }

        return $fileDescriptorLister->listFileDescriptors();
    }
}
