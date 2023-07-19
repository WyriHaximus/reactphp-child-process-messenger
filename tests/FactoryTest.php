<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use React\ChildProcess\Process;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory as MessagesFactory;
use WyriHaximus\React\ChildProcess\Messenger\MessengerInterface;
use WyriHaximus\React\ChildProcess\Messenger\ReturnChild;
use WyriHaximus\TestUtilities\TestCase;

use function Clue\React\Block\await;
use function gc_collect_cycles;

final class FactoryTest extends TestCase
{
    protected LoopInterface $loop;

    protected Process $process;

    public function setUp(): void
    {
        $this->loop    = Loop::get();
        $this->process = $this->prophesize(Process::class)->reveal();
    }

    public function tearDown(): void
    {
        unset($this->process, $this->loop);
    }

    public function testParentFromClassException(): void
    {
        self::expectException(Throwable::class);
        self::expectExceptionMessage('Given class doesn\'t implement ChildInterface');
        Factory::parentFromClass('stdClass', $this->prophesize(LoopInterface::class)->reveal());
    }

    /**
     * @test
     */
    public function parentFromClassActualRun(): void
    {
        $payload = await(
            Factory::parentFromClass(ReturnChild::class, $this->loop)->then(
                static function (MessengerInterface $messenger): PromiseInterface {
                    return $messenger->rpc(MessagesFactory::rpc('return', ['foo' => 'bar']));
                }
            ),
            $this->loop
        );
        self::assertSame(['foo' => 'bar'], $payload->getPayload());
    }

    public function testNoGarbageCollectionAfterSuccessfulRun(): void
    {
        gc_collect_cycles();
        $payload = await(
            Factory::parentFromClass(ReturnChild::class, $this->loop)->then(
                static function (MessengerInterface $messenger): PromiseInterface {
                    return $messenger->rpc(MessagesFactory::rpc('return', ['foo' => 'bar']));
                }
            ),
            $this->loop
        );
        $this->assertSame(0, gc_collect_cycles());
    }
}
