<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess;
use WyriHaximus\React\ChildProcess\Messenger\MessengerInterface;
use WyriHaximus\React\ChildProcess\Messenger\OutstandingCallInterface;
use WyriHaximus\TestUtilities\TestCase;

use function Safe\json_encode;

final class RpcSuccessTest extends TestCase
{
    public function testBasic(): void
    {
        $payload = new Payload(['foo' => 'bar']);
        $message = new RpcSuccess('abc', $payload);

        self::assertSame($payload, $message->getPayload());

        self::assertEquals('{"type":"rpc_success","uniqid":"abc","payload":{"foo":"bar"}}', json_encode($message));

        $outstandingCall = $this->prophesize(OutstandingCallInterface::class);
        $messenger       = $this->prophesize(MessengerInterface::class);
        $messenger->getOutstandingCall('abc')->shouldBeCalled()->willReturn($outstandingCall->reveal());

        $message->handle($messenger->reveal(), '');
    }
}
