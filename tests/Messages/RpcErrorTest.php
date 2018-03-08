<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Phake;
use PHPUnit\Framework\TestCase;
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
            \WyriHaximus\throwable_json_encode($payload) .
            '}',
            json_encode($message)
        );

        $outstandingCall = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\OutstandingCall');
        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->getOutstandingCall('abc')->thenReturn($outstandingCall);

        $message->handle($messenger, '');

        Phake::inOrder(
            Phake::verify($messenger)->getOutstandingCall('abc'),
            Phake::verify($outstandingCall)->reject($payload)
        );
    }
}
