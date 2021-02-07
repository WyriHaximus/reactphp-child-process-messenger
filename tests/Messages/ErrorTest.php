<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Exception;
use WyriHaximus\TestUtilities\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;

use function Safe\json_encode;

final class ErrorTest extends TestCase
{
    public function testBasic(): void
    {
        $payload = new Exception('foo:bar');
        $message = new Error($payload);

        self::assertSame($payload, $message->getPayload());

        self::assertEquals('{"type":"error","payload":{"foo":"bar"}}', json_encode($message));

        $em = $this->prophesize('Evenement\EventEmitter');
        $em->emit('error', [$payload, $em])->shouldBeCalled();

        $message->handle($em->reveal(), '');
    }
}
