<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Exception;
use Prophecy\Argument;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Messenger\MessengerInterface;
use WyriHaximus\TestUtilities\TestCase;

use function React\Promise\reject;
use function React\Promise\resolve;
use function Safe\json_encode;

final class RpcTest extends TestCase
{
    public function testBasic(): void
    {
        $payload = new Payload(['foo' => 'bar']);
        $message = new Rpc('foo', $payload);

        self::assertSame($payload, $message->getPayload());

        self::assertEquals('{"type":"rpc","uniqid":"","target":"foo","payload":{"foo":"bar"}}', json_encode($message));
        self::assertEquals('{"type":"rpc","uniqid":"bar","target":"foo","payload":{"foo":"bar"}}', json_encode($message->setUniqid('bar')));
    }

    public function testHasNoRpcTarget(): void
    {
        $payload = new Payload(['foo' => 'bar']);
        $message = new Rpc('foo', $payload, 'bar');

        $messenger = $this->prophesize(MessengerInterface::class);
        $messenger->write('')->shouldBeCalled();
        $messenger->hasRpc('foo')->shouldBeCalled()->willReturn(true);
        $messenger->createLine(Argument::type('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError'))->shouldBeCalled()->willReturn('');
        $messenger->callRpc('foo', $payload)->shouldBeCalled()->willReturn(reject(new Exception('foo:bar')));

        $message->handle($messenger->reveal(), '');
    }

    public function testSuccess(): void
    {
        $payload = new Payload(['foo' => 'bar']);
        $message = new Rpc('foo', $payload, 'bar');

        $messenger = $this->prophesize(MessengerInterface::class);
        $messenger->write('')->shouldBeCalled();
        $messenger->hasRpc('foo')->shouldBeCalled()->willReturn(true);
        $messenger->createLine(Argument::type('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess'))->shouldBeCalled()->willReturn('');
        $messenger->callRpc('foo', $payload)->shouldBeCalled()->willReturn(resolve([
            'a',
            'b',
            'c',
        ]));

        $message->handle($messenger->reveal(), '');
    }

    public function testError(): void
    {
        $payload = new Payload(['foo' => 'bar']);
        $message = new Rpc('foo', $payload, 'bar');

        $messenger = $this->prophesize(MessengerInterface::class);
        $messenger->write('')->shouldBeCalled();
        $messenger->hasRpc('foo')->shouldBeCalled()->willReturn(true);
        $messenger->createLine(Argument::type('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError'))->shouldBeCalled()->willReturn('');
        $messenger->callRpc('foo', $payload)->shouldBeCalled()->willReturn(reject(new Exception()));

        $message->handle($messenger->reveal(), '');
    }
}
