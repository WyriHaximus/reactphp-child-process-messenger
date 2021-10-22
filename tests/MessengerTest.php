<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Exception;
use Prophecy\Argument;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\ChildProcess\Options;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\ChildProcess\Messenger\ProcessUnexpectedEndException;
use WyriHaximus\React\Tests\ChildProcess\Messenger\Stub\ConnectionStub;
use WyriHaximus\TestUtilities\TestCase;

use function Clue\React\Block\await;
use function React\Promise\resolve;

final class MessengerTest extends TestCase
{
    public function testSetAndHasRpc(): void
    {
        $connection = $this->prophesize(ConnectionInterface::class);
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $messenger = new Messenger($connection->reveal(), new Options('rng', '127.0.0.1:13', 1));

        $payload       = [
            'a',
            'b',
            'c',
        ];
        $callableFired = false;
        $callable      = static function (Payload $passedPayload) use (&$callableFired): PromiseInterface {
            $callableFired = true;

            return resolve($passedPayload->getPayload());
        };

        $messenger->registerRpc('test', $callable);
        self::assertFalse($messenger->hasRpc('tset'));
        self::assertTrue($messenger->hasRpc('test'));

        self::assertSame($payload, await($messenger->callRpc('test', new Payload($payload)), Loop::get()));

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

        $messenger = new Messenger($connection->reveal(), new Options('rng', '127.0.0.1:13', 1));

        $messenger->message(Factory::message(['foo' => 'bar']));
    }

    public function testError(): void
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal(), new Options('rng', '127.0.0.1:13', 1));

        $messenger->error(Factory::error(new Exception('foo:bar')));
    }

    public function testRpc(): void
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal(), new Options('rng', '127.0.0.1:13', 1));

        $messenger->rpc(Factory::rpc('target', ['foo' => 'bar']));
    }

    public function testEmitOnData(): void
    {
        $connection = new ConnectionStub();

        $cbCalled = false;
        (new Messenger($connection, new Options('rng', '127.0.0.1:13', 1)))->on('data', static function ($data) use (&$cbCalled): void {
            self::assertEquals('bar.foo', $data);
            $cbCalled = true;
        });

        $connection->emit('data', ['bar.foo']);

        self::assertTrue($cbCalled);
    }

    public function testCrashed(): void
    {
        self::expectException(ProcessUnexpectedEndException::class);

        $connection = new ConnectionStub();

        $messenger = new Messenger($connection, new Options('rng', '127.0.0.1:13', 1));
        $messenger->on('error', static function (Throwable $throwable): void {
            throw $throwable;
        });
        $messenger->crashed(123);
    }
}
