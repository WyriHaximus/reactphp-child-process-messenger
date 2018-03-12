<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class ErrorTest extends TestCase
{
    public function testBasic()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Error($payload);

        $this->assertSame($payload, $message->getPayload());

        $this->assertEquals('{"type":"error","payload":{"foo":"bar"}}', json_encode($message));

        $em = $this->prophesize('Evenement\EventEmitter');
        $em->emit('error', [$payload, $em])->shouldBeCalled();

        $message->handle($em->reveal(), '');
    }
}
