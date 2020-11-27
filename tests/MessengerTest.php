<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\TestUtilities\TestCase;
use Prophecy\Argument;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Messenger\ProcessUnexpectedEndException;
use WyriHaximus\React\Tests\ChildProcess\Messenger\Stub\ConnectionStub;

use function Clue\React\Block\await;
use function React\Promise\Stream\first;

final class MessengerTest extends TestCase
{
    public function testSetAndHasRpc(): void
    {
        $connection = $this->prophesize(ConnectionInterface::class);
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $messenger = new Messenger($connection->reveal());

        $payload       = [
            'a',
            'b',
            'c',
        ];
        $callableFired = false;
        $callable      = function ($passedPayload) use (&$callableFired, $payload): void {
            self::assertEquals($payload, $passedPayload);
            $callableFired = true;
        };

        $messenger->registerRpc('test', $callable);
        self::assertFalse($messenger->hasRpc('tset'));
        self::assertTrue($messenger->hasRpc('test'));

        $messenger->callRpc('test', new Payload($payload));

        self::assertTrue($callableFired);

        $messenger->deregisterRpc('test');
        self::assertFalse($messenger->hasRpc('test'));
    }

    public function testMessage(): void
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal());

        $messenger->message(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message(['foo' => 'bar']));
    }

    public function testError(): void
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal());

        $messenger->error(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::error(new \Exception('foo:bar')));
    }

    public function testRpc(): void
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal());

        $messenger->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('target', ['foo' => 'bar']));
    }

    public function testEmitOnData(): void
    {
        $connection = new ConnectionStub();

        $cbCalled = false;
        (new Messenger($connection))->on('data', function ($data) use (&$cbCalled): void {
            self::assertEquals('bar.foo', $data);
            $cbCalled = true;
        });

        $connection->emit('data', ['bar.foo']);

        self::assertTrue($cbCalled);
    }

    public function testCrashed(): void
    {
        self::expectException(ProcessUnexpectedEndException::class);

        $loop       = Factory::create();
        $connection = new ConnectionStub();

        $messenger = new Messenger($connection);
        $loop->futureTick(static function () use ($messenger): void {
            $messenger->crashed(123);
        });

        throw await(first($messenger, 'error'), $loop); /** @phpstan-ignore-line  */
    }
}
