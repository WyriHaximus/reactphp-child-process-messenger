<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Stream\Stream;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class Recipient extends EventEmitter
{
    use OnDataTrait;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Stream
     */
    protected $stdin;

    /**
     * @var Stream
     */
    protected $stdout;

    /**
     * @var Stream
     */
    protected $stderr;

    /**
     * @var array
     */
    protected $rpcs = [];

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;

        $this->setupStreams();
    }

    /**
     * @param $target
     * @param callable $listener
     */
    public function registerRpc($target, callable $listener)
    {
        $this->rpcs[$target] = $listener;
    }

    protected function setupStreams()
    {
        $this->stdin = new Stream(STDIN, $this->loop);
        $this->stdout = new Stream(STDOUT, $this->loop);
        $this->stderr = new Stream(STDERR, $this->loop);

        $this->stdin->on('data', function ($data) {
            $this->onData($data, 'stdin');
        });
    }

    /**
     * @param string $data
     */
    public function write($data)
    {
        $this->stdout->write($data);
    }

    /**
     * @param string $data
     */
    public function error($data)
    {
        $this->stderr->write($data);
    }

    protected function handleMessage(array $message, $source)
    {
        switch ($message['type']) {
            case 'rpc':
                $this->handleRpc($message['target'], $message['payload'], $message['uniqid']);
                break;
        }
    }

    protected function handleRpc($target, $payload, $uniqid)
    {
        if (!isset($this->rpcs[$target])) {
            return $this->rpcError($uniqid, 'Target doesn\'t exist');
        }

        $invoke = new Invoke($this->loop, new Payload($payload));
        $invoke->getPromise()->then(function ($payload) use ($uniqid) {
            $this->rpcSuccess($uniqid, $payload);
        }, null, function ($payload) use ($uniqid) {
            $this->rpcNotify($uniqid, $payload);
        });

        $this->rpcs[$target]($invoke);
    }

    protected function rpcError($uniqid, $message)
    {
        $this->stderr->write(json_encode([
            'uniqid' => $uniqid,
            'payload' => $message,
        ]) . PHP_EOL);
    }

    protected function rpcSuccess($uniqid, $payload)
    {
        $this->stdout->write(json_encode([
            'type' => 'rpc_result',
            'uniqid' => $uniqid,
            'payload' => $payload,
        ]) . PHP_EOL);
    }

    protected function rpcNotify($uniqid, $payload)
    {
        $this->stdout->write(json_encode([
            'type' => 'rpc_notify',
            'uniqid' => $uniqid,
            'payload' => $payload,
        ]) . PHP_EOL);
    }
}
