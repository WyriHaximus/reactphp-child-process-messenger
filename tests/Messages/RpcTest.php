<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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

        $messenger = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        $messenger->write('')->shouldBeCalled();
        $messenger->hasRpc('foo')->shouldBeCalled()->willReturn(true);
        $messenger->createLine(Argument::type('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError'))->shouldBeCalled()->willReturn('');
        $messenger->callRpc('foo', $payload)->shouldBeCalled()->willReturn(\React\Promise\reject([
            'foo' => 'bar',
        ]));

        $message->handle($messenger->reveal(), '');
    }

    public function testSuccess()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $messenger = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        $messenger->write('')->shouldBeCalled();
        $messenger->hasRpc('foo')->shouldBeCalled()->willReturn(true);
        $messenger->createLine(Argument::type('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess'))->shouldBeCalled()->willReturn('');
        $messenger->callRpc('foo', $payload)->shouldBeCalled()->willReturn(\React\Promise\resolve([
            'a',
            'b',
            'c',
        ]));

        $message->handle($messenger->reveal(), '');
    }

    public function testError()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Rpc('foo', $payload, 'bar');

        $messenger = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        $messenger->write('')->shouldBeCalled();
        $messenger->hasRpc('foo')->shouldBeCalled()->willReturn(true);
        $messenger->createLine(Argument::type('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError'))->shouldBeCalled()->willReturn('');
        $messenger->callRpc('foo', $payload)->shouldBeCalled()->willReturn(\React\Promise\reject(new \Exception()));

        $message->handle($messenger->reveal(), '');
    }
}
