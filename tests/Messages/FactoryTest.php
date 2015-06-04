<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function providerFromLine()
    {
        yield [
            '{"type":"message","payload":["foo","bar"]}' . Line::EOL,
            function ($message) {
                $this->assertInstanceOf(Message::class, $message);
                $this->assertInstanceOf(Payload::class, $message->getPayload());
                $this->assertSame([
                    'foo',
                    'bar',
                ], $message->getPayload()->getPayload());
                return true;
            },
        ];
    }

    /**
     * @dataProvider providerFromLine
     */
    public function testFromLine($input, callable $tests)
    {
        $line = Factory::fromLine($input, []);
        $this->assertTrue($tests($line));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unknown message type: massage
     */
    public function testFromLineException()
    {
        Factory::fromLine('{"type":"massage","payload":["foo","bar"]}' . LineInterface::EOL, []);
    }

    public function providerMessage()
    {
        yield [
            [],
        ];

        yield [
            [
                'food' => 'truck',
            ],
        ];
    }

    /**
     * @dataProvider providerMessage
     */
    public function testMessage(array $payload)
    {
        $message = Factory::message($payload);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(Payload::class, $message->getPayload());
        $this->assertSame($payload, $message->getPayload()->getPayload());
    }
}
