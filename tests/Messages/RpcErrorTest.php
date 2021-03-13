<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Exception;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineEncoder;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError;
use WyriHaximus\React\ChildProcess\Messenger\MessengerInterface;
use WyriHaximus\React\ChildProcess\Messenger\OutstandingCallInterface;
use WyriHaximus\TestUtilities\TestCase;

use function Safe\json_encode;

final class RpcErrorTest extends TestCase
{
    public function testBasic(): void
    {
        $payload = new Exception('foo.bar');
        $message = new RpcError('abc', $payload);

        self::assertSame($payload, $message->getPayload());

        self::assertEquals(
            '{"type":"rpc_error","uniqid":"abc","payload":' .
            json_encode(LineEncoder::encode($payload)) .
            '}',
            json_encode($message)
        );

        $outstandingCall = $this->prophesize(OutstandingCallInterface::class);
        $messenger       = $this->prophesize(MessengerInterface::class);
        $messenger->getOutstandingCall('abc')->shouldBeCalled()->willReturn($outstandingCall->reveal());

        $message->handle($messenger->reveal(), '');
    }
}
