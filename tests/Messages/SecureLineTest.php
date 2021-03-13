<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Messenger\Messages\SecureLine;
use WyriHaximus\TestUtilities\TestCase;

use function Safe\json_decode;
use function Safe\json_encode;

final class SecureLineTest extends TestCase
{
    public const KEY = 'abc';

    /**
     * @return iterable<array<Rpc|string>>
     */
    public function providerBasic(): iterable
    {
        return [
            [
                new Rpc(
                    'foo',
                    new Payload(['bar' => 'baz']),
                    'wasedrftgyhujiko'
                ),
                '{"type":"secure","line":"{\"type\":\"rpc\",\"uniqid\":\"wasedrftgyhujiko\",\"target\":\"foo\",\"payload\":{\"bar\":\"baz\"}}","signature":"LPupf5C96orwYVDNK0Fbd3bGc1aUkLid+iCmcNuZ6Ms="}' . LineInterface::EOL,
                '{"type":"rpc","uniqid":"wasedrftgyhujiko","target":"foo","payload":{"bar":"baz"}}',
            ],
            [
                new Rpc(
                    'foo',
                    new Payload([
                        'bar',
                        'baz',
                    ]),
                    'wasedrftgyhujiko'
                ),
                '{"type":"secure","line":"{\"type\":\"rpc\",\"uniqid\":\"wasedrftgyhujiko\",\"target\":\"foo\",\"payload\":[\"bar\",\"baz\"]}","signature":"n6VBaCjLsuUuISTRC0+IreYuBm0WXRdVSRnbIO\/NlP4="}' . LineInterface::EOL,
                '{"type":"rpc","uniqid":"wasedrftgyhujiko","target":"foo","payload":["bar","baz"]}',
            ],
        ];
    }

    /**
     * @dataProvider providerBasic
     */
    public function testBasic(ActionableMessageInterface $input, string $output, string $lineString): void
    {
        $line = new SecureLine($input, [
            'key' => self::KEY,
        ]);
        self::assertEquals($output, (string) $line);

        $stringLine = SecureLine::fromLine(json_decode((string) $line, true), [
            'key' => self::KEY,
        ]);
        self::assertEquals($lineString, json_encode($stringLine));
    }

    public function testSignatureMismatch(): void
    {
        self::expectException(Throwable::class);
        self::expectExceptionMessage('Signature mismatch!');
        $line = '{"type":"secure","line":"{\"type\":\"rpc\",\"uniqid\":\"wasedrftgyhujiko\",\"target\":\"foo\",\"payload\":[\"bar\",\"baz\"]}","signature":"n6VBaCjLsuUuISTRC0+IreYuBm0WXRdVSRnbIO\/NlP4="}';
        SecureLine::fromLine(json_decode($line, true), ['key' => 'cba']);
    }
}
