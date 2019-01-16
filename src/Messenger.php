<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use Evenement\EventEmitter;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use React\Socket\ConnectionInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

class Messenger extends EventEmitter
{
    const INTERVAL = 0.1;
    const TERMINATE_RPC = 'wyrihaximus.react.child-process.messenger.terminate';

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var OutstandingCalls
     */
    protected $outstandingRpcCalls;

    /**
     * @var array
     */
    protected $rpcs = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string[]
     */
    protected $buffer = '';

    protected $defaultOptions = [
        'lineClass' => 'WyriHaximus\React\ChildProcess\Messenger\Messages\Line',
        'messageFactoryClass' => 'WyriHaximus\React\ChildProcess\Messenger\Messages\Factory',
        'lineOptions' => [],
    ];

    /**
     * Messenger constructor.
     * @param ConnectionInterface $connection
     * @param array               $options
     */
    public function __construct(
        ConnectionInterface $connection,
        array $options = []
    ) {
        $this->connection = $connection;

        $this->options = $this->defaultOptions + $options;

        $this->outstandingRpcCalls = new OutstandingCalls();

        $this->connection->on('data', function ($data) {
            $this->buffer .= $data;
            $this->emit('data', [$data]);
            $this->handleData();
        });
        $this->connection->on('close', function () {
            $calls = $this->outstandingRpcCalls->getCalls();
            if (\count($calls) === 0) {
                return;
            }
            $error = new CommunicationWithProcessUnexpectedEndException();
            $this->emit('error', [$error, $this]);
            /** @var OutstandingCall $call */
            foreach ($calls as $call) {
                $call->reject($error);
            }
        });
    }

    /**
     * @param string   $target
     * @param callable $listener
     */
    public function registerRpc($target, callable $listener)
    {
        $this->rpcs[$target] = $listener;
    }

    /**
     * @param string $target
     */
    public function deregisterRpc($target)
    {
        unset($this->rpcs[$target]);
    }

    /**
     * @param  string $target
     * @return bool
     */
    public function hasRpc($target)
    {
        return isset($this->rpcs[$target]);
    }

    /**
     * @param $target
     * @param $payload
     * @return React\Promise\PromiseInterface
     */
    public function callRpc($target, $payload)
    {
        try {
            $promise = $this->rpcs[$target]($payload, $this);
            if ($promise instanceof PromiseInterface) {
                return $promise;
            }

            throw new \Exception('RPC must return promise');
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        } catch (\Throwable $exception) {
            return new RejectedPromise($exception);
        }
    }

    /**
     * @param Message $message
     */
    public function message(Message $message)
    {
        $this->write($this->createLine($message));
    }

    /**
     * @param Error $error
     */
    public function error(Error $error)
    {
        $this->write($this->createLine($error));
    }

    /**
     * @param  string          $uniqid
     * @return OutstandingCall
     */
    public function getOutstandingCall($uniqid)
    {
        return $this->outstandingRpcCalls->getCall($uniqid);
    }

    /**
     * @param  Rpc                    $rpc
     * @return \React\Promise\Promise
     */
    public function rpc(Rpc $rpc)
    {
        $callReference = $this->outstandingRpcCalls->newCall(function () {
        });

        $this->write($this->createLine($rpc->setUniqid($callReference->getUniqid())));

        return $callReference->getDeferred()->promise();
    }

    /**
     * @param  ActionableMessageInterface $line
     * @return LineInterface
     */
    public function createLine(ActionableMessageInterface $line)
    {
        $lineCLass = $this->options['lineClass'];

        return (string) new $lineCLass($line, $this->options['lineOptions']);
    }

    /**
     * @return \React\Promise\Promise
     */
    public function softTerminate()
    {
        return $this->rpc(MessagesFactory::rpc(static::TERMINATE_RPC));
    }

    /**
     * @param string $line
     */
    public function write($line)
    {
        $this->connection->write($line);
    }

    /**
     * @param int|null $exitCode
     * @internal
     */
    public function crashed($exitCode)
    {
        $this->emit('error', [new ProcessUnexpectedEndException($exitCode), $this]);
    }

    private function handleData()
    {
        if (\strpos($this->buffer, LineInterface::EOL) === false) {
            return;
        }

        $messages = \explode(LineInterface::EOL, $this->buffer);
        $this->buffer = \array_pop($messages);
        $this->iterateMessages($messages);
    }

    private function iterateMessages(array $messages)
    {
        foreach ($messages as $message) {
            try {
                MessagesFactory::fromLine($message, [])->handle($this, 'source');
            } catch (\Exception $exception) {
                $this->emit('error', [$exception, $this]);
            } catch (\Throwable $exception) {
                $this->emit('error', [$exception, $this]);
            }
        }
    }
}
