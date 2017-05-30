<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Phake;
use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class FactoryTest extends TestCase
{
    /**
     * @var React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var React\ChildProcess\Process
     */
    protected $process;

    public function setUp()
    {
        $this->loop = \React\EventLoop\Factory::create();
        $this->process = Phake::mock('React\ChildProcess\Process');
        $this->process->stdin = Phake::mock('React\Stream\Stream');
        $this->process->stdout = Phake::mock('React\Stream\Stream');
        $this->process->stderr = Phake::mock('React\Stream\Stream');
    }

    public function tearDown()
    {
        unset($this->process, $this->loop);
    }

    public function testParent()
    {
        Phake::when($this->process)->isRunning(null)->thenReturn(true);
        Phake::when($this->process)->getCommand()->thenReturn('abc');

        $messengerPromise = Factory::parent($this->process, $this->loop);
        $this->assertInstanceOf('React\Promise\PromiseInterface', $messengerPromise);
        $cbFired = false;
        $messengerPromise->then(function ($messenger) use (&$cbFired) {
            $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messenger', $messenger);
            $this->assertSame($this->process->stdin, $messenger->getStdin());
            $this->assertSame($this->process->stdout, $messenger->getStdout());
            $this->assertSame($this->process->stderr, $messenger->getStderr());

            $this->assertEquals('abc', $messenger->getCommand());

            $cbFired = true;
        });

        $this->loop->run();

        $this->assertTrue($cbFired);

        Phake::inOrder(
            Phake::verify($this->process)->isRunning(null)
        );
    }

    public function testChild()
    {
        $this->loop = Phake::mock('React\EventLoop\LoopInterface');
        $messenger = Factory::child($this->loop);
        $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messenger', $messenger);
        $this->assertInstanceOf('React\Stream\Stream', $messenger->getStdin());
        $this->assertInstanceOf('React\Stream\Stream', $messenger->getStdout());
        $this->assertInstanceOf('React\Stream\Stream', $messenger->getStderr());
        $messenger->callRpc('wyrihaximus.react.child-process.messenger.terminate', new Payload([]));
        Phake::verify($this->loop)->addTimer(
            1,
            [
                $this->loop,
                'stop',
            ]
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Given class doesn't implement ChildInterface
     */
    public function testParentFromClassException()
    {
        Factory::parentFromClass('stdClass', Phake::mock('React\EventLoop\LoopInterface'));
    }

    public function testParentFromClassActualRun()
    {
        $ranMessengerCreateCallback = false;
        $ranChildProcessCallback = false;
        $loop = \React\EventLoop\Factory::create();
        Factory::parentFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)->then(function (Messenger $messenger) use (&$ranMessengerCreateCallback, &$ranChildProcessCallback) {
            $ranMessengerCreateCallback = true;
            $messenger->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('return', [
                'foo' => 'bar',
            ]))->then(function (Payload $payload) use (&$ranChildProcessCallback, $messenger) {
                $this->assertSame([
                    'foo' => 'bar',
                ], $payload->getPayload());
                $ranChildProcessCallback = true;
                $messenger->softTerminate();
            });
        });
        $loop->run();
        $this->assertTrue($ranMessengerCreateCallback);
        $this->assertTrue($ranChildProcessCallback);
    }
}
