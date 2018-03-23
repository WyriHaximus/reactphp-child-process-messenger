<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\Tests\ChildProcess\Messenger\Stub\ConnectionStub;

class MessengerTest extends TestCase
{
    public function testSetAndHasRpc()
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $messenger = new Messenger($connection->reveal());

        $payload = [
            'a',
            'b',
            'c',
        ];
        $callableFired = false;
        $callable = function (array $passedPayload) use (&$callableFired, $payload) {
            $this->assertEquals($payload, $passedPayload);
            $callableFired = true;
        };

        $messenger->registerRpc('test', $callable);
        $this->assertFalse($messenger->hasRpc('tset'));
        $this->assertTrue($messenger->hasRpc('test'));

        $messenger->callRpc('test', $payload);

        $this->assertTrue($callableFired);

        $messenger->deregisterRpc('test');
        $this->assertFalse($messenger->hasRpc('test'));
    }

    public function testMessage()
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal());

        $messenger->message(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
            'foo' => 'bar',
        ]));
    }

    public function testError()
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal());

        $messenger->error(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::error([
            'foo' => 'bar',
        ]));
    }

    public function testRpc()
    {
        $connection = $this->prophesize('React\Socket\ConnectionInterface');
        $connection->on('data', Argument::type('callable'))->shouldBeCalled();
        $connection->on('close', Argument::type('callable'))->shouldBeCalled();
        $connection->write(Argument::type('string'))->shouldBeCalled();

        $messenger = new Messenger($connection->reveal());

        $messenger->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('target', [
            'foo' => 'bar',
        ]));
    }

    public function testEmitOnData()
    {
        $connection = new ConnectionStub();

        $cbCalled = false;
        (new Messenger($connection))->on('data', function ($data) use (&$cbCalled) {
            $this->assertEquals('bar.foo', $data);
            $cbCalled = true;
        });

        $connection->emit('data', ['bar.foo']);

        $this->assertTrue($cbCalled);
    }

    public function testCrashed()
    {
        $this->setExpectedException('WyriHaximus\React\ChildProcess\Messenger\ProcessUnexpectedEndException');

        $loop = Factory::create();
        $connection = new ConnectionStub();

        $messenger = new Messenger($connection);
        $loop->futureTick(function () use ($messenger) {
            $messenger->crashed(123);
        });

        throw \Clue\React\Block\await(\React\Promise\Stream\first($messenger, 'error'), $loop);
    }
}
