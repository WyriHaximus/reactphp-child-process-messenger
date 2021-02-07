<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use Exception;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\Server;
use RuntimeException;
use WyriHaximus\FileDescriptors\Factory as FileDescriptorsFactory;
use WyriHaximus\FileDescriptors\ListerInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessengesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

use function assert;
use function bin2hex;
use function escapeshellarg;
use function explode;
use function hash_hmac;
use function is_subclass_of;
use function random_bytes;
use function Safe\sprintf;
use function WyriHaximus\React\futurePromise;

use const DIRECTORY_SEPARATOR;
use const PHP_BINARY;
use const PHP_EOL;
use const PHP_SAPI;

final class  Factory
{
    public const INTERVAL                = 0.1;
    public const TIMEOUT                 = 13;
    public const TERMINATE_TIMEOUT       = 1;
    public const PROCESS_REGISTER        = 'wyrihaximus.react.child-process.messenger.child.register';
    public const DEFAULT_CONNECT_TIMEOUT = 15;

    /**
     * @param array<mixed> $options
     */
    public static function parent(
        Process $process,
        LoopInterface $loop,
        array $options = []
    ): Promise\PromiseInterface {
        return new Promise\Promise(static function ($resolve, $reject) use ($process, $loop, $options): void {
            $server = new Server('127.0.0.1:0', $loop);

            $options['random']  = bin2hex(random_bytes(32));
            $options['address'] = $server->getAddress();
            $argvString         = escapeshellarg(ArgvEncoder::encode($options));
            $process            = new Process($process->getCommand() . ' ' . $argvString);

            self::startParent($process, $server, $loop, $options)->done($resolve, $reject); /** @phpstan-ignore-line  */
        });
    }

    /**
     * @param  array<mixed> $options
     *
     * @return Promise\PromiseInterface<Messenger>
     *
     * @phpstan-ignore-next-line
     */
    public static function parentFromClass(
        string $class,
        LoopInterface $loop,
        array $options = []
    ): Promise\PromiseInterface {
        if (! is_subclass_of($class, ChildInterface::class)) {
            throw new Exception('Given class doesn\'t implement ChildInterface'); /** @phpstan-ignore-line  */
        }

        return new Promise\Promise(static function ($resolve, $reject) use ($class, $loop, $options): void {
            $server             = new Server('127.0.0.1:0', $loop);
            $options['random']  = bin2hex(random_bytes(32));
            $options['address'] = $server->getAddress();

            $template = '%s';
            if (array_key_exists('cmdTemplate', $options)) {
                $template = $options['cmdTemplate'];
                unset($options['cmdTemplate']);
            }

            $fds = [];
            if (StaticConfig::shouldListFileDescriptors() && DIRECTORY_SEPARATOR !== '\\') {
                if (array_key_exists('fileDescriptorLister', $options) && $options['fileDescriptorLister'] instanceof ListerInterface) {
                    $fileDescriptorLister = $options['fileDescriptorLister'];
                    unset($options['fileDescriptorLister']);
                } else {
                    $fileDescriptorLister = FileDescriptorsFactory::create();
                }

                foreach ($fileDescriptorLister->list() as $id) {
                    $fds[(int) $id] = ['file', '/dev/null', 'r'];
                }
            }

            $phpBinary        = escapeshellarg(PHP_BINARY . (PHP_SAPI === 'phpdbg' ? ' -qrr --' : ''));
            $childProcessPath = escapeshellarg(__DIR__ . DIRECTORY_SEPARATOR . 'child-process.php');
            $argvString       = escapeshellarg(ArgvEncoder::encode($options));
            $command          = $phpBinary . ' ' . $childProcessPath;

            $process = new Process(
                sprintf(
                    $template,
                    $command . ' ' . $argvString
                ),
                null,
                null,
                $fds
            );

            $connectTimeout = $options['connect-timeout'] ?? self::DEFAULT_CONNECT_TIMEOUT;
            futurePromise($loop)->then(static function () use ($process, $server, $loop, $options, $connectTimeout): Promise\PromiseInterface { /** @phpstan-ignore-line  */
                return Promise\Timer\timeout(self::startParent($process, $server, $loop, $options), $connectTimeout, $loop);
            })->then(static function (Messenger $messenger) use ($class): Promise\PromiseInterface {
                return $messenger->rpc(MessengesFactory::rpc(Factory::PROCESS_REGISTER, ['className' => $class]))->then(static function ($p) use ($messenger): Promise\PromiseInterface {
                    return Promise\resolve($messenger);
                });
            })->done($resolve, $reject);
        });
    }

    /**
     * @param  array<mixed> $options
     *
     * @return Promise\PromiseInterface<Messenger>
     *
     * @phpstan-ignore-next-line
     */
    public static function child(LoopInterface $loop, array $options = [], ?callable $termiteCallable = null): Promise\PromiseInterface
    {
        $connectTimeout = $options['connect-timeout'] ?? self::DEFAULT_CONNECT_TIMEOUT;

        return (new Connector($loop, ['timeout' => $connectTimeout]))->connect($options['address'])->then(static function (ConnectionInterface $connection) use ($options, $loop, $connectTimeout): Promise\PromiseInterface {
            return new Promise\Promise(static function ($resolve, $reject) use ($connection, $options, $loop, $connectTimeout): void {
                Promise\Timer\timeout(Promise\Stream\first($connection), $connectTimeout, $loop)->then(static function ($chunk) use ($resolve, $reject, $connection, $options, $loop): void {
                    [$confirmation] = explode(PHP_EOL, $chunk);
                    if ($confirmation === 'syn') {
                        $connection->write('ack' . PHP_EOL);
                        $resolve(new Messenger($connection, $options));
                        $connection->on('close', [$loop, 'stop']);
                        $connection->on('error', [$loop, 'stop']);

                        return;
                    }

                    $reject(new RuntimeException('Handshake SYN failed'));
                }, $reject);
                $connection->write(hash_hmac('sha512', $options['address'], $options['random']) . PHP_EOL);
            });
        })->then(static function (Messenger $messenger) use ($loop, $termiteCallable) {
            if ($termiteCallable === null) {
                $termiteCallable = static function () use ($loop): void {
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
                static function (Payload $payload, Messenger $messenger) use ($termiteCallable): Promise\PromiseInterface {
                    $messenger->emit('terminate', [$messenger]);
                    $termiteCallable($payload, $messenger);

                    return Promise\resolve([]);
                }
            );

            return $messenger;
        });
    }

    /**
     * @param array<mixed> $options
     *
     * @return Promise\Promise|Promise\PromiseInterface
     */
    private static function startParent(
        Process $process,
        Server $server,
        LoopInterface $loop,
        array $options
    ) {
        return (new Promise\Promise(static function ($resolve, $reject) use ($process, $server, $loop, $options): void {
            $server->on(
                'connection',
                static function (ConnectionInterface $connection) use ($server, $resolve, $reject, $options): void {
                    Promise\Stream\first($connection)->then(static function ($chunk) use ($options, $connection): Promise\PromiseInterface { /** @phpstan-ignore-line  */
                        [$confirmation] = explode(PHP_EOL, $chunk);
                        if ($confirmation === hash_hmac('sha512', $options['address'], $options['random'])) {
                            $connection->write('syn' . PHP_EOL);

                            return Promise\Stream\first($connection);
                        }

                        return Promise\reject(new RuntimeException('Signature mismatch'));
                    })->then(static function ($chunk) use ($options, $connection): Promise\PromiseInterface {
                        [$confirmation] = explode(PHP_EOL, $chunk);
                        if ($confirmation === 'ack') {
                            return Promise\resolve(new Messenger($connection, $options));
                        }

                        return Promise\reject(new RuntimeException('Handshake failed'));
                    })->always(static function () use ($server): void {
                        $server->close();
                    })->done($resolve, $reject);
                }
            );
            $server->on('error', static function ($et) use ($reject): void {
                $reject($et);
            });

            $process->start($loop);
        }, static function () use ($server, $process): void {
            $server->close();
            $process->terminate();
        }))->then(static function (Messenger $messenger) use ($loop, $process): Messenger {
            $loop->addPeriodicTimer(self::INTERVAL, static function ($timer) use ($messenger, $loop, $process): void {
                if ($process->isRunning()) {
                    return;
                }

                $loop->cancelTimer($timer);

                $exitCode = $process->getExitCode();
                if ($exitCode === 0) {
                    return;
                }

                if ($exitCode !== null) {
                    $messenger->crashed($exitCode);
                }
            });

            return $messenger;
        });
    }
}
