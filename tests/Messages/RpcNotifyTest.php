<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\TestUtilities\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcNotify;

use function Safe\json_encode;

final class RpcNotifyTest extends TestCase
{
    public function testBasic(): void
    {
        $payload = new Payload(['foo' => 'bar']);
        $message = new RpcNotify('abc', $payload);

        self::assertSame($payload, $message->getPayload());

        self::assertEquals('{"type":"rpc_notify","uniqid":"abc","payload":{"foo":"bar"}}', json_encode($message));

        $outstandingCall = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\OutstandingCall');
        $messenger       = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        $messenger->getOutstandingCall('abc')->shouldBeCalled()->willReturn($outstandingCall->reveal());

        $message->handle($messenger->reveal(), '');
    }
}
