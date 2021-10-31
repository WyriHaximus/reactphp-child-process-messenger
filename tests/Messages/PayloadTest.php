<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\TestUtilities\TestCase;

final class PayloadTest extends TestCase
{
    public function testBasic(): void
    {
        $payload = new Payload(['foo' => 'bar']);

        self::assertEquals(['foo' => 'bar'], $payload->getPayload());
        // @phpstan-ignore-next-line - https://github.com/phpstan/phpstan-phpunit/issues/100
        self::assertArrayHasKey('foo', $payload);
        self::assertEquals('bar', $payload['foo']);
        $payload['ajsdhjkfad'] = 'abc';
        self::assertEquals('abc', $payload['ajsdhjkfad']);
        unset($payload['ajsdhjkfad']);
        self::assertArrayNotHasKey('ajsdhjkfad', $payload);
        $payload[] = 'abc';
        self::assertEquals('abc', $payload[0]);
        self::assertEquals([
            'foo' => 'bar',
            0 => 'abc',
        ], $payload->getPayload());
    }
}
