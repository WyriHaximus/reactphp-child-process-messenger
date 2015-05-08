<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use Evenement\EventEmitter;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class Messenger extends EventEmitter
{
    const INTERVAL = 0.1;

    use OnDataTrait;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var float
     */
    protected $interval;

    /**
     * @var OutstandingCalls
     */
    protected $outstandingRpcCalls;

    /**
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
        $this->outstandingRpcCalls = new OutstandingCalls();
    }

    /**
     * @param LoopInterface $loop
     * @param float $interval
     * @return PromiseInterface
     */
    public function start(LoopInterface $loop, $interval = self::INTERVAL)
    {
        $this->loop = $loop;
        $this->interval = $interval;

        $this->process->start($this->loop, $this->interval);
        $this->attachMessenger();

        return \WyriHaximus\React\tickingPromise($this->loop, $this->interval, [$this, 'isRunning'])->then(function () {
            return \React\Promise\resolve($this);
        });
    }

    protected function attachMessenger()
    {
        $this->process->stdout->on('data', function ($data) {
            $this->onData($data, 'stdout');
        });
        $this->process->stderr->on('data', function ($data) {
            $this->onData($data, 'stderr');
        });
    }

    /**
     * @param array $message
     * @param string $source
     */
    protected function handleMessage($message, $source)
    {
        if ($message === null) {
            return;
        }

        if ($source == 'stderr' && isset($message['uniqid'])) {
            $this->outstandingRpcCalls->getCall($message['uniqid'])->getDeferred()->reject($message['payload']);
            return;
        }

        if ($source == 'stderr' && !isset($message['uniqid'])) {
            $this->emit('error', [
                $message['payload'],
                $this,
            ]);
            return;
        }

        switch ($message['type']) {
            case 'message':
                $this->emit('message', [
                    $message['payload'],
                    $this,
                ]);
                break;
            case 'rpc_result':
                $this->outstandingRpcCalls->getCall($message['uniqid'])->getDeferred()->resolve($message['payload']);
                break;
            case 'rpc_error':
                $this->outstandingRpcCalls->getCall($message['uniqid'])->getDeferred()->reject($message['payload']);
                break;
            case 'rpc_notify':
                $this->outstandingRpcCalls->getCall($message['uniqid'])->getDeferred()->notify($message['payload']);
                break;
        }
    }

    /**
     * @param array $message
     */
    public function message(Payload $message)
    {
        $this->process->stdin->write(json_encode([
            'type' => 'message',
            'payload' => $message->getPayload(),
        ]) . PHP_EOL);
    }

    public function rpc(Call $call)
    {
        $callReference = $this->outstandingRpcCalls->newCall(function () {

        });

        $this->process->stdin->write(json_encode([
            'type' => 'rpc',
            'uniqid' => $callReference->getUniqid(),
            'target' => $call->getTarget(),
            'payload' => $call->getMessage()->getPayload(),
        ]) . PHP_EOL);

        return $callReference->getDeferred()->promise();
    }

    public function close()
    {
        $this->process->close();
    }

    public function terminate($signal = null)
    {
        return $this->process->terminate($signal);
    }

    public function getCommand()
    {
        return $this->process->getCommand();
    }

    final public function getEnhanceSigchildCompatibility()
    {
        return $this->process->getEnhanceSigchildCompatibility();
    }

    final public function setEnhanceSigchildCompatibility($enhance)
    {
        return $this->process->setEnhanceSigchildCompatibility($enhance);
    }

    public function getExitCode()
    {
        return $this->process->getExitCode();
    }

    public function getPid()
    {
        return $this->process->getPid();
    }

    public function getStopSignal()
    {
        return $this->process->getStopSignal();
    }

    public function getTermSignal()
    {
        return $this->process->getTermSignal();
    }

    public function isRunning()
    {
        return $this->process->isRunning();
    }

    public function isStopped()
    {
        return $this->process->isStopped();
    }

    public function isTerminated()
    {
        return $this->process->isTerminated();
    }

    public function getProcess()
    {
        return $this->process;
    }
}
