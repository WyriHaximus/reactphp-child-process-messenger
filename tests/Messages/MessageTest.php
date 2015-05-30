<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Phake;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Message($payload);

        $this->assertSame($payload, $message->getPayload());

        $this->assertEquals('{"type":"message","payload":{"foo":"bar"}}', json_encode($message));

        $em = Phake::mock('Evenement\EventEmitter');

        $message->handle($em, '');

        Phake::verify($em)->emit('message', [$payload, $em]);
    }
}
