<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\ChildProcess;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\ChildInterface;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

use function is_subclass_of;
use function React\Promise\resolve;

final class Process
{
    private LoopInterface $loop;

    private Messenger $messenger;

    private function __construct(LoopInterface $loop, Messenger $messenger)
    {
        $this->loop      = $loop;
        $this->messenger = $messenger;
        $this->messenger->registerRpc(
            MessengerFactory::PROCESS_REGISTER,
            function (Payload $payload): PromiseInterface {
                /**
                 * @psalm-suppress PossiblyNullArgument
                 */
                if (! is_subclass_of($payload['className'], ChildInterface::class)) {
                    throw DoesNotImplementChildInterfaceException::create($payload['className'] ?? '');
                }

                ($payload['className'])::create($this->messenger, $this->loop);
                $this->deregisterRpc();

                return resolve([]);
            }
        );
    }

    public static function create(LoopInterface $loop, Messenger $messenger): void
    {
        $reject = static function (Throwable $exeption) use ($loop): void {
//            $messenger->error(MessagesFactory::error($exeption->getFile()));
            $loop->addTimer(1, static function () use ($loop): void {
                $loop->stop();
            });
        };

        try {
            new Process($loop, $messenger);
        } catch (Throwable $throwable) { /** @phpstan-ignore-line */
            $reject($throwable);
        }
    }

    private function deregisterRpc(): void
    {
        $this->loop->futureTick(function (): void {
            $this->messenger->deregisterRpc(MessengerFactory::PROCESS_REGISTER);
        });
    }
}
