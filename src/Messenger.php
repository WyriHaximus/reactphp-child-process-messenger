<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\ChildProcess\Options;
use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

use function array_key_exists;
use function array_pop;
use function count;
use function explode;
use function React\Promise\reject;
use function strpos;

final class Messenger implements MessengerInterface, EventEmitterInterface
{
    use EventEmitterTrait;

    public const INTERVAL      = 0.1;
    public const TERMINATE_RPC = 'wyrihaximus.react.child-process.messenger.terminate';

    protected ConnectionInterface $connection;

    protected OutstandingCalls $outstandingRpcCalls;

    /** @var array<mixed> */
    protected array $rpcs = [];

    /** @var array<mixed> */
    protected array $options = [];

    protected string $buffer = '';

    /** @var array<string, class-string|array<mixed>> */
    protected array $defaultOptions = [
        'lineClass' => Line::class,
        'messageFactoryClass' => MessagesFactory::class,
        'lineOptions' => [],
    ];

    public function __construct(
        ConnectionInterface $connection,
        Options $options
    ) {
        $this->connection = $connection;

        /**
         * @psalm-suppress InvalidMethodCall
         */
        $this->options = $this->defaultOptions + $options->toArray();

        $this->outstandingRpcCalls = new OutstandingCalls();

        $this->connection->on('data', function (string $data): void {
            $this->buffer .= $data;
            $this->emit('data', [$data]);
            $this->handleData();
        });
        $this->connection->on('close', function (): void {
            $calls = $this->outstandingRpcCalls->getCalls();
            if (count($calls) === 0) {
                return;
            }

            $error = new CommunicationWithProcessUnexpectedEndException();
            $this->emit('error', [$error, $this]);
            foreach ($calls as $call) {
                $call->reject($error);
            }
        });
    }

    public function registerRpc(string $target, callable $listener): void
    {
        $this->rpcs[$target] = $listener;
    }

    public function deregisterRpc(string $target): void
    {
        unset($this->rpcs[$target]);
    }

    public function hasRpc(string $target): bool
    {
        return array_key_exists($target, $this->rpcs);
    }

    public function callRpc(string $target, Payload $payload): PromiseInterface
    {
        try {
            return $this->rpcs[$target]($payload, $this);

            /** @phpstan-ignore-next-line  */
        } catch (Throwable $exception) {
            return reject($exception);
        }
    }

    public function message(Message $message): void
    {
        $this->write($this->createLine($message));
    }

    public function error(Error $error): void
    {
        $this->write($this->createLine($error));
    }

    public function getOutstandingCall(string $uniqid): OutstandingCallInterface
    {
        return $this->outstandingRpcCalls->getCall($uniqid);
    }

    public function rpc(Rpc $rpc): PromiseInterface
    {
        $callReference = $this->outstandingRpcCalls->newCall(function (): void {}); // phpcs:disabled

        $this->write($this->createLine($rpc->setUniqid($callReference->getUniqid())));

        return $callReference->getDeferred()->promise();
    }

    public function createLine(ActionableMessageInterface $line): string
    {
        $lineCLass = $this->options['lineClass'];

        /**
         * @psalm-suppress InvalidCast
         */
        return (string) new $lineCLass($line, $this->options['lineOptions']);
    }

    public function softTerminate(): PromiseInterface
    {
        return $this->rpc(MessagesFactory::rpc(self::TERMINATE_RPC));
    }

    public function write(string $line): void
    {
        $this->connection->write($line);
    }

    /**
     * @internal
     */
    public function crashed(int $exitCode): void
    {
        $this->emit('error', [new ProcessUnexpectedEndException($exitCode), $this]);
    }

    private function handleData(): void
    {
        if (strpos($this->buffer, LineInterface::EOL) === false) {
            return;
        }

        $messages     = explode(LineInterface::EOL, $this->buffer);
        $this->buffer = array_pop($messages);
        $this->iterateMessages($messages);
    }

    /**
     * @param array<string> $messages
     */
    private function iterateMessages(array $messages): void
    {
        foreach ($messages as $message) {
            try {
                MessagesFactory::fromLine($message, [])->handle($this, 'source');
                /** @phpstan-ignore-next-line  */
            } catch (Throwable $exception) {
                $this->emit('error', [$exception, $this]);
            }
        }
    }
}
