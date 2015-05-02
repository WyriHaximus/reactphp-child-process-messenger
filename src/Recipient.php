<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Stream\Stream;

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
    protected $in;

    /**
     * @var Stream
     */
    protected $out;

    /**
     * @var Stream
     */
    protected $err;

    /**
     * @var array
     */
    protected $rpcs = [];

    /**
     * @var string[]
     */
    protected $buffers = [
        'in' => '',
    ];

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
        $this->in = new Stream(STDIN, $this->loop);
        $this->out = new Stream(STDOUT, $this->loop);
        $this->err = new Stream(STDERR, $this->loop);

        $this->in->on('data', function ($data) {
            $this->onData($data, 'in');
        });
    }

    /**
     * @param string $data
     */
    public function write($data)
    {
        $this->out->write($data);
    }

    /**
     * @param string $data
     */
    public function error($data)
    {
        $this->err->write($data);
    }

    protected function handleMessage($message)
    {
        switch ($message->type) {
            case 'rpc':
                $this->handleRpc($message->target, $message->payload, $message->uniqid);
                break;
        }
    }

    protected function handleRpc($target, $payload, $uniqid)
    {
        if (!isset($this->rpcs[$target])) {
            return $this->rpcError($uniqid, 'Target doesn\'t exist');
        }

        $this->rpcs[$target]($payload)->then(function ($payload) use ($uniqid) {
            $this->rpcSuccess($uniqid, $payload);
        }, null, function ($payload) use ($uniqid) {
            $this->rpcNotify($uniqid, $payload);
        });
    }

    protected function rpcError($uniqid, $message)
    {
        $this->err->write(json_encode([
            'uniqid' => $uniqid,
            'payload' => $message,
        ]) . PHP_EOL);
    }

    protected function rpcSuccess($uniqid, $payload)
    {
        $this->out->write(json_encode([
            'type' => 'rpc_result',
            'uniqid' => $uniqid,
            'payload' => $payload,
        ]) . PHP_EOL);
    }

    protected function rpcNotify($uniqid, $payload)
    {
        $this->out->write(json_encode([
            'type' => 'rpc_notify',
            'uniqid' => $uniqid,
            'payload' => $payload,
        ]) . PHP_EOL);
    }

    protected function envelopeMessage($data)
    {
        //return
    }
}
