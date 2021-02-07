<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\TestUtilities\TestCase;
use React\ChildProcess\Process;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

final class FactoryTest extends TestCase
{
    protected LoopInterface $loop;

    protected Process $process;

    public function setUp(): void
    {
        $this->loop    = EventLoopFactory::create();
        $this->process = $this->prophesize('React\ChildProcess\Process')->reveal();
    }

    public function tearDown(): void
    {
        unset($this->process, $this->loop);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Given final class doesn't implement ChildInterface
     */
    public function testParentFromClassException(): void
    {
        Factory::parentFromClass('stdClass', $this->prophesize('React\EventLoop\LoopInterface')->reveal());
    }

    /**
     * @test
     */
    public function parentFromClassActualRun(): void
    {
        $ranMessengerCreateCallback = false;
        $ranChildProcessCallback    = false;
        $loop                       = \React\EventLoop\Factory::create();
        Factory::parentFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)->then(static function (Messenger $messenger) use (&$ranMessengerCreateCallback, &$ranChildProcessCallback): void {
            $ranMessengerCreateCallback = true;
            $messenger->rpc(MessagesFactory::rpc('return', ['foo' => 'bar']))->then(static function (Payload $payload) use (&$ranChildProcessCallback, $messenger): void {
                self::assertSame(['foo' => 'bar'], $payload->getPayload());
                $ranChildProcessCallback = true;
                $messenger->softTerminate();
            });
        })->done();
        $loop->run();
        self::assertTrue($ranMessengerCreateCallback);
        self::assertTrue($ranChildProcessCallback);
    }
}
