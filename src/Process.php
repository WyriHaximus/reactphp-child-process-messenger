<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use Exception;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

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
                if (! is_subclass_of($payload['className'], ChildInterface::class)) {
                    throw new Exception('Given class doesn\'t implement ChildInterface'); /**

























            @phpstan-ignore-line */
                }

                $this->registerClass($payload['className']); /** @phpstan-ignore-line */
                $this->deregisterRpc();

                return resolve([]);
            }
        );
    }

    public static function create(LoopInterface $loop, Messenger $messenger): Process
    {
        $reject = static function ($exeption) use ($messenger, $loop): void {
            $messenger->error(MessagesFactory::error($exeption->getFile()));
            $loop->addTimer(1, static function () use ($loop): void {
                $loop->stop();
            });
        };

        try {  /** @phpstan-ignore-line  */
            return new Process($loop, $messenger);
        } catch (Throwable $throwable) { /** @phpstan-ignore-line  */
            $reject($throwable);
        }
    }

    /**
     * @param class-string<ChildInterface> $className
     */
    private function registerClass(string $className): void
    {
        ($className . '::create')($this->messenger, $this->loop); /** @phpstan-ignore-line  */
    }

    private function deregisterRpc(): void
    {
        $this->loop->futureTick(function (): void {
            $this->messenger->deregisterRpc(MessengerFactory::PROCESS_REGISTER);
        });
    }
}
