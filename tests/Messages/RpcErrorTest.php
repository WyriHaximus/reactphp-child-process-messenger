<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineEncoder;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError;

class RpcErrorTest extends TestCase
{
    public function testBasic()
    {
        $payload = new \Exception('foo.bar');
        $message = new RpcError('abc', $payload);

        $this->assertSame($payload, $message->getPayload());

        $this->assertEquals(
            '{"type":"rpc_error","uniqid":"abc","payload":' .
            json_encode(LineEncoder::encode($payload)) .
            '}',
            json_encode($message)
        );

        $outstandingCall = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\OutstandingCall');
        $messenger = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        $messenger->getOutstandingCall('abc')->shouldBeCalled()->wilLReturn($outstandingCall->reveal());

        $message->handle($messenger->reveal(), '');
    }
}
