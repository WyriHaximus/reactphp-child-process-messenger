<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Exception;
use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineEncoder;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess;
use WyriHaximus\TestUtilities\TestCase;

use function Safe\json_encode;

final class FactoryTest extends TestCase
{
    public const KEY = 'abc';

    /**
     * @return iterable<array<string|callable|array>>
     */
    public function providerFromLine(): iterable
    {
        $exception = new Exception('angry unicorn');

        return [
            [
                '{"type":"message","payload":["foo","bar"]}' . LineInterface::EOL,
                static function (Message $message): bool {
                    self::assertSame([
                        'foo',
                        'bar',
                    ], $message->getPayload()->getPayload());

                    return true;
                },
            ],
            [
                '{"type":"error","payload":' . json_encode(LineEncoder::encode($exception)) . '}' . LineInterface::EOL,
                static function (Error $message) use ($exception): bool {
                    self::assertEquals($exception, $message->getPayload());

                    return true;
                },
            ],
            [
                '{"type":"rpc","uniqid":"abc","target":"foo","payload":["foo","bar"]}' . LineInterface::EOL,
                static function (Rpc $message): bool {
                    self::assertEquals([
                        'type' => 'rpc',
                        'uniqid' => 'abc',
                        'target' => 'foo',
                        'payload' => new Payload([
                            'foo',
                            'bar',
                        ]),
                    ], $message->jsonSerialize());

                    return true;
                },
            ],
            [
                '{"type":"rpc_error","uniqid":"abc","payload":' . json_encode(LineEncoder::encode($exception)) . '}' . LineInterface::EOL,
                static function (RpcError $message) use ($exception): bool {
                    self::assertInstanceOf('Exception', $message->getPayload());
                    $messageJson = $message->jsonSerialize();
                    unset($messageJson['payload']['c']['originalTrace']);
                    $expected = [
                        'type' => 'rpc_error',
                        'uniqid' => 'abc',
                        'payload' => LineEncoder::encode($exception),
                    ];
                    unset($expected['payload']['c']['originalTrace']);
                    self::assertEquals($expected, $messageJson);

                    return true;
                },
            ],
            [
                '{"type":"rpc_success","uniqid":"abc","payload":["foo","bar"]}' . LineInterface::EOL,
                static function (RpcSuccess $message): bool {
                    self::assertEquals([
                        'type' => 'rpc_success',
                        'uniqid' => 'abc',
                        'payload' => new Payload([
                            'foo',
                            'bar',
                        ]),
                    ], $message->jsonSerialize());

                    return true;
                },
            ],
            [
                '{"type":"secure","line":"{\"type\":\"rpc\",\"uniqid\":\"abc\",\"target\":\"foo\",\"payload\":[\"bar\",\"baz\"]}","signature":"4GF1+eHnzORvlAlWsNDT0dZiYHAaXxEyrgWJ2MRNA3M="}' . LineInterface::EOL,
                static function (Rpc $message): bool {
                    self::assertEquals([
                        'type' => 'rpc',
                        'uniqid' => 'abc',
                        'payload' => new Payload([
                            'bar',
                            'baz',
                        ]),
                        'target' => 'foo',
                    ], $message->jsonSerialize());

                    return true;
                },
                [
                    'key' => self::KEY,
                ],
            ],
        ];
    }

    /**
     * @param mixed        $input
     * @param array<mixed> $lineOptions
     *
     * @dataProvider providerFromLine
     */
    public function testFromLine($input, callable $tests, array $lineOptions = []): void
    {
        $line = Factory::fromLine($input, $lineOptions);
        self::assertTrue($tests($line));
    }

    public function testFromLineException(): void
    {
        self::expectException(Throwable::class);
        self::expectExceptionMessage('Unknown message type: massage');
        Factory::fromLine('{"type":"massage","payload":["foo","bar"]}' . LineInterface::EOL, []);
    }
}
