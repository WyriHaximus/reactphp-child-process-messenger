<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class MessageTest extends TestCase
{
    public function testBasic()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Message($payload);

        $this->assertSame($payload, $message->getPayload());

        $this->assertEquals('{"type":"message","payload":{"foo":"bar"}}', \json_encode($message));

        $em = $this->prophesize('Evenement\EventEmitter');
        $em->emit('message', [$payload, $em])->shouldBeCalled();

        $message->handle($em->reveal(), '');
    }
}
