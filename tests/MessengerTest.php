<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Phake;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory as EventLoopFactory;
use React\Stream\ReadableResourceStream;
use React\Stream\Stream;
use React\Stream\WritableResourceStream;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class MessengerTest extends TestCase
{
    public function testSetAndHasRpc()
    {
        $loop = EventLoopFactory::create();
        $messenger = Factory::child($loop);

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

    public function testGetters()
    {
        $loop = \React\EventLoop\Factory::create();
        $stdin = new ReadableResourceStream(STDIN, $loop);
        $stdout = new WritableResourceStream(STDOUT, $loop);
        $stderr = new WritableResourceStream(STDERR, $loop);

        $messenger = new Messenger($stdin, $stdout, $stderr, []);

        $this->assertSame($stdin, $messenger->getStdin());
        $this->assertSame($stdout, $messenger->getStdout());
        $this->assertSame($stderr, $messenger->getStderr());
    }

    public function testMessage()
    {
        $loop = \React\EventLoop\Factory::create();
        $stdin = Phake::mock('React\Stream\Stream');
        $stdout = new WritableResourceStream(STDOUT, $loop);
        $stderr = new WritableResourceStream(STDERR, $loop);

        $messenger = new Messenger($stdin, $stdout, $stderr, [
            'write' => 'stdin',
        ]);

        $messenger->message(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([
            'foo' => 'bar',
        ]));

        Phake::verify($stdin)->write($this->isType('string'));
    }

    public function testError()
    {
        $loop = \React\EventLoop\Factory::create();
        $stdin = new ReadableResourceStream(STDIN, $loop);
        $stdout = new WritableResourceStream(STDOUT, $loop);
        $stderr = Phake::mock('React\Stream\Stream');

        $messenger = new Messenger($stdin, $stdout, $stderr, [
            'write_err' => 'stderr',
        ]);

        $messenger->error(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::error([
            'foo' => 'bar',
        ]));

        Phake::verify($stderr)->write($this->isType('string'));
    }

    public function testRpc()
    {
        $loop = \React\EventLoop\Factory::create();
        $stdin = Phake::mock('React\Stream\ReadableStreamInterface');
        $stdout = new WritableResourceStream(STDOUT, $loop);
        $stderr = new WritableResourceStream(STDERR, $loop);

        $messenger = new Messenger($stdin, $stdout, $stderr, [
            'write' => 'stdin',
        ]);

        $messenger->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('target', [
            'foo' => 'bar',
        ]));

        Phake::verify($stdin)->write($this->isType('string'));
    }

    public function testOnData()
    {
        $loop = \React\EventLoop\Factory::create();
        $stdin = Phake::mock('React\Stream\ReadableStreamInterface');

        Phake::when($stdin)->on('data', $this->isType('callable'))->thenGetReturnByLambda(function ($target, $callback) {
            $callback((string)new Line(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::message([]), []));
        });

        $stdout = new WritableResourceStream(STDOUT, $loop);
        $stderr = new WritableResourceStream(STDERR, $loop);

        $messenger = new Messenger($stdin, $stdout, $stderr, [
            'read' => 'stdin',
        ]);
    }

    public function testEmitOnData()
    {
        $loop = \React\EventLoop\Factory::create();

        $stdin = new ReadableResourceStream(STDIN, $loop);
        $stdout = new WritableResourceStream(STDOUT, $loop);
        $stderr = new WritableResourceStream(STDERR, $loop);

        $cbCalled = false;
        (new Messenger($stdin, $stdout, $stderr, [
            'read' => 'stdin',
        ]))->on('data', function ($source, $data) use (&$cbCalled, $loop) {
            $this->assertEquals('stdin', $source);
            $this->assertEquals('bar.foo', $data);
            $cbCalled = true;
        });

        $stdin->emit('data', ['bar.foo']);

        $this->assertTrue($cbCalled);
    }
}
