<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\TestUtilities\TestCase;

use function Safe\json_encode;

final class MessageTest extends TestCase
{
    public function testBasic(): void
    {
        $payload = new Payload(['foo' => 'bar']);
        $message = new Message($payload);

        self::assertSame($payload, $message->getPayload());

        self::assertEquals('{"type":"message","payload":{"foo":"bar"}}', json_encode($message));

        $em = $this->prophesize('Evenement\EventEmitter');
        $em->emit('message', [$payload, $em])->shouldBeCalled();

        $message->handle($em->reveal(), '');
    }
}
