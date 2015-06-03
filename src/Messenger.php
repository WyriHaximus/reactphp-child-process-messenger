<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use Evenement\EventEmitter;
use React\Promise\Deferred;
use React\Stream\Stream;
use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

class Messenger extends EventEmitter
{
    const INTERVAL = 0.1;

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
    protected $buffers = [
        'stdin' => '',
        'stdout' => '',
        'stderr' => '',
    ];

    protected $defaultOptions = [
        'lineClass' => Line::class,
        'lineOptions' => [],
    ];

    /**
     * @param Stream $stdin
     * @param Stream $stdout
     * @param Stream $stderr
     * @param array $options
     */
    public function __construct(Stream $stdin, Stream $stdout, Stream $stderr, array $options)
    {
        $this->stdin  = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->options = $this->defaultOptions + $options;

        $this->outstandingRpcCalls = new OutstandingCalls();

        $this->attachMessenger();
    }

    /**
     * @param $target
     * @param callable $listener
     */
    public function registerRpc($target, callable $listener)
    {
        $this->rpcs[$target] = $listener;
    }

    /**
     * @param string $target
     * @return bool
     */
    public function hasRpc($target)
    {
        return isset($this->rpcs[$target]);
    }

    /**
     * @param string $target
     * @param mixed $payload
     * @param Deferred $deferred
     */
    public function callRpc($target, $payload, Deferred $deferred)
    {
        $this->rpcs[$target]($payload, $deferred);
    }

    protected function attachMessenger()
    {
        /**
         * @todo duplicated code much?
         */
        if (isset($this->options['read_err'])) {
            $streamName = $this->options['read_err'];
            $this->$streamName->on('data', function ($data) use ($streamName) {
                $this->onData($data, $streamName);
            });
            unset($streamName);
        }

        if (isset($this->options['read'])) {
            $streamName = $this->options['read'];
            $this->$streamName->on('data', function ($data) use ($streamName) {
                $this->onData($data, $streamName);
            });
            unset($streamName);
        }
    }

    /**
     * @param string $line
     */
    protected function write($line)
    {
        if (isset($this->options['write'])) {
            $streamName = $this->options['write'];
            $this->$streamName->write($line);
            unset($streamName);
        }
    }

    /**
     * @param string $line
     */
    protected function writeErr($line)
    {
        if (isset($this->options['write_err'])) {
            $streamName = $this->options['write_err'];
            $this->$streamName->write($line);
            unset($streamName);
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
     * @param string $uniqid
     * @return OutstandingCall
     */
    public function getOutstandingCall($uniqid)
    {
        return $this->outstandingRpcCalls->getCall($uniqid);
    }

    /**
     * @param Rpc $rpc
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
     * @param string $data
     * @param string $source
     */
    protected function onData($data, $source)
    {
        $this->buffers[$source] .= $data;

        if (strpos($this->buffers[$source], LineInterface::EOL) !== false) {
            $messages = explode(LineInterface::EOL, $this->buffers[$source]);
            $this->buffers[$source] = array_pop($messages);
            $this->iterateMessages($messages, $source);
        }
    }

    /**
     * @param array $messages
     * @param string $source
     */
    protected function iterateMessages(array $messages, $source)
    {
        foreach ($messages as $message) {
            MessageFactory::fromLine($message, $this->options['lineOptions'])->handle($this, $source);
        }
    }

    /**
     * @param ActionableMessageInterface $line
     * @return LineInterface
     */
    public function createLine(ActionableMessageInterface $line)
    {
        $lineCLass = $this->options['lineClass'];
        return (string) new $lineCLass($line, $this->options['lineOptions']);
    }

    /**
     * @return Stream
     */
    public function getStdin()
    {
        return $this->stdin;
    }

    /**
     * @return Stream
     */
    public function getStdout()
    {
        return $this->stdout;
    }

    /**
     * @return Stream
     */
    public function getStderr()
    {
        return $this->stderr;
    }

    /**
     * Forward any unknown calls when there is a call forward possible.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (isset($this->options['callForward'])) {
            $call = $this->options['callForward'];
            return $call($name, $arguments);
        }
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
