<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Phake;
use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\React\Tests\ChildProcess\Messenger\Stub\ConnectionStub;

class MessengerTest extends TestCase
{
    public function testSetAndHasRpc()
    {
        $connection = Phake::mock('React\Socket\ConnectionInterface');
        $messenger = new Messenger($connection);

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
        $connection = Phake::mock('React\Socket\ConnectionInterface');

        $messenger = new Messenger($connection);

        $messenger->message(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
            'foo' => 'bar',
        ]));

        Phake::verify($connection)->write($this->isType('string'));
    }

    public function testError()
    {
        $connection = Phake::mock('React\Socket\ConnectionInterface');

        $messenger = new Messenger($connection);

        $messenger->error(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::error([
            'foo' => 'bar',
        ]));

        Phake::verify($connection)->write($this->isType('string'));
    }

    public function testRpc()
    {
        $connection = Phake::mock('React\Socket\ConnectionInterface');

        $messenger = new Messenger($connection);

        $messenger->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('target', [
            'foo' => 'bar',
        ]));

        Phake::verify($connection)->write($this->isType('string'));
    }

    public function testOnData()
    {
        $connection = Phake::mock('React\Socket\ConnectionInterface');

        Phake::when($connection)->on('data', $this->isType('callable'))->thenGetReturnByLambda(function ($target, $callback) {
            $callback((string)new Line(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([]), []));
        });

        new Messenger($connection);
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
}
