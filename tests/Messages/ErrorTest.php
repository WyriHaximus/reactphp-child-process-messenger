<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Phake;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $payload = new Payload([
            'foo' => 'bar',
        ]);
        $message = new Error($payload);

        $this->assertSame($payload, $message->getPayload());

        $this->assertEquals('{"type":"error","payload":{"foo":"bar"}}', json_encode($message));

        $em = Phake::mock('Evenement\EventEmitter');

        $message->handle($em, '');

        Phake::verify($em)->emit('error', [$payload, $em]);
    }
}
