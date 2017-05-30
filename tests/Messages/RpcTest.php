<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Phake;
use PHPUnit\Framework\TestCase;
use React\Promise\RejectedPromise;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

class RpcTest extends TestCase
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

        $stream = Phake::mock('React\Stream\Stream');
        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->hasRpc('foo')->thenReturn(false);
        Phake::when($messenger)->getStderr()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError'))->thenReturn('');

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

        $stream = Phake::mock('React\Stream\Stream');
        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->hasRpc('foo')->thenReturn(true);
        Phake::when($messenger)->getStdout()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess'))->thenReturn('');
        $callbackFired = false;
        Phake::when($messenger)->callRpc('foo', $payload)->thenGetReturnByLambda(function ($target, $payload) use (&$callbackFired) {
            $callbackFired = true;

            return \React\Promise\resolve([
                'a',
                'b',
                'c',
            ]);
        });

        $message->handle($messenger, '');

        $this->assertTrue($callbackFired);

        Phake::inOrder(
            Phake::verify($messenger)->hasRpc('foo'),
            Phake::verify($messenger)->getStdout(),
            Phake::verify($messenger)->createLine($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess')),
            Phake::verify($stream)->write($this->isType('string'))
        );
    }

    public function testError()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $stream = Phake::mock('React\Stream\Stream');
        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->hasRpc('foo')->thenReturn(true);
        Phake::when($messenger)->getStderr()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError'))->thenReturn('');
        Phake::when($messenger)->callRpc('foo', $payload)->thenReturn(new RejectedPromise(new \Exception()));

        $message->handle($messenger, '');

        Phake::inOrder(
            Phake::verify($messenger)->hasRpc('foo'),
            Phake::verify($messenger)->getStderr(),
            Phake::verify($messenger)->createLine($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError')),
            Phake::verify($stream)->write($this->isType('string'))
        );
    }

    public function testNotify()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $stream = Phake::mock('React\Stream\Stream');
        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->hasRpc('foo')->thenReturn(true);
        Phake::when($messenger)->getStdout()->thenReturn($stream);
        Phake::when($messenger)->createLine($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcNotify'))->thenReturn('');
        $callbackFired = false;
        Phake::when($messenger)->callRpc('foo', $payload)->thenGetReturnByLambda(function ($target, $payload) use (&$callbackFired) {
            $callbackFired = true;
            $promise = Phake::partialMock('React\Promise\Promise', function () {
            });
            Phake::when($promise)->then($this->isType('callable'), $this->isType('callable'), $this->isType('callable'))->thenGetReturnByLambda(function ($yes, $no, $notify) {
                return $notify([
                    'a',
                    'b',
                    'c',
                ]);
            });

            return $promise;
        });

        $message->handle($messenger, '');

        $this->assertTrue($callbackFired);

        Phake::inOrder(
            Phake::verify($messenger)->hasRpc('foo'),
            Phake::verify($messenger)->getStdout(),
            Phake::verify($messenger)->createLine($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcNotify')),
            Phake::verify($stream)->write($this->isType('string'))
        );
    }
}
