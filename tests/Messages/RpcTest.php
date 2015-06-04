<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Phake;
use React\Promise\Deferred;
use React\Stream\Stream;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcNotify;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class RpcTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload);

        $this->assertSame($payload, $message->getPayload());

        $this->assertEquals('{"type":"rpc","uniqid":"","target":"foo","payload":{"foo":"bar"}}', json_encode($message));
        $this->assertEquals('{"type":"rpc","uniqid":"bar","target":"foo","payload":{"foo":"bar"}}', json_encode($message->setUniqid('bar')));
    }

    public function testHasNoRpcTarget()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $stream = Phake::mock(Stream::class);
        $messenger = Phake::mock(Messenger::class);
        Phake::when($messenger)->hasRpc('foo')->thenReturn(false);
        Phake::when($messenger)->getStderr()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf(RpcError::class))->thenReturn('');

        $message->handle($messenger, '');

        Phake::inOrder(
            Phake::verify($messenger)->hasRpc('foo'),
            Phake::verify($stream)->write($this->isType('string'))
        );
    }

    public function testSuccess()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $stream = Phake::mock(Stream::class);
        $messenger = Phake::mock(Messenger::class);
        Phake::when($messenger)->hasRpc('foo')->thenReturn(true);
        Phake::when($messenger)->getStdout()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf(RpcSuccess::class))->thenReturn('');
        $callbackFired = false;
        Phake::when($messenger)->callRpc('foo', $payload, $this->isInstanceOf(Deferred::class))->thenGetReturnByLambda(function ($target, $payload, $deferred) use (&$callbackFired) {
            $deferred->resolve([
                'a',
                'b',
                'c',
            ]);
            $callbackFired = true;
        });

        $message->handle($messenger, '');

        $this->assertTrue($callbackFired);

        Phake::inOrder(
            Phake::verify($messenger)->hasRpc('foo'),
            Phake::verify($messenger)->getStdout(),
            Phake::verify($messenger)->createLine($this->isInstanceOf(RpcSuccess::class)),
            Phake::verify($stream)->write($this->isType('string'))
        );
    }

    public function testError()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $stream = Phake::mock(Stream::class);
        $messenger = Phake::mock(Messenger::class);
        Phake::when($messenger)->hasRpc('foo')->thenReturn(true);
        Phake::when($messenger)->getStderr()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf(RpcError::class))->thenReturn('');
        Phake::when($messenger)->callRpc('foo', $payload, $this->isInstanceOf(Deferred::class))->thenThrow(new \Exception());

        $message->handle($messenger, '');

        Phake::inOrder(
            Phake::verify($messenger)->hasRpc('foo'),
            Phake::verify($messenger)->getStderr(),
            Phake::verify($messenger)->createLine($this->isInstanceOf(RpcError::class)),
            Phake::verify($stream)->write($this->isType('string'))
        );
    }

    public function testNotify()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $stream = Phake::mock(Stream::class);
        $messenger = Phake::mock(Messenger::class);
        Phake::when($messenger)->hasRpc('foo')->thenReturn(true);
        Phake::when($messenger)->getStdout()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf(RpcNotify::class))->thenReturn('');
        $callbackFired = false;
        Phake::when($messenger)->callRpc('foo', $payload, $this->isInstanceOf(Deferred::class))->thenGetReturnByLambda(function ($target, $payload, $deferred) use (&$callbackFired) {
            $deferred->progress([
                'a',
                'b',
                'c',
            ]);
            $callbackFired = true;
        });

        $message->handle($messenger, '');

        $this->assertTrue($callbackFired);

        Phake::inOrder(
        Phake::verify($messenger)->hasRpc('foo'),
        Phake::verify($messenger)->getStdout(),
        Phake::verify($messenger)->createLine($this->isInstanceOf(RpcNotify::class)),
        Phake::verify($stream)->write($this->isType('string'))
        );
    }
}
