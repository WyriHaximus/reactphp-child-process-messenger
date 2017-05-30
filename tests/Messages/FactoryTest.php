<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class FactoryTest extends TestCase
{
    const KEY = 'abc';

    public function providerFromLine()
    {
        return [
            [
                '{"type":"message","payload":["foo","bar"]}' . LineInterface::EOL,
                function ($message) {
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Message', $message);
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Payload', $message->getPayload());
                    $this->assertSame([
                        'foo',
                        'bar',
                    ], $message->getPayload()->getPayload());

                    return true;
                },
            ],
            [
                '{"type":"error","payload":["foo","bar"]}' . LineInterface::EOL,
                function ($message) {
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Error', $message);
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Payload', $message->getPayload());
                    $this->assertSame([
                        'foo',
                        'bar',
                    ], $message->getPayload()->getPayload());

                    return true;
                },
            ],
            [
                '{"type":"rpc","uniqid":"abc","target":"foo","payload":["foo","bar"]}' . LineInterface::EOL,
                function ($message) {
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc', $message);
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Payload', $message->getPayload());
                    $this->assertEquals([
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
                '{"type":"rpc_error","uniqid":"abc","payload":["foo","bar"]}' . LineInterface::EOL,
                function ($message) {
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError', $message);
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Payload', $message->getPayload());
                    $this->assertEquals([
                        'type' => 'rpc_error',
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
                '{"type":"rpc_success","uniqid":"abc","payload":["foo","bar"]}' . LineInterface::EOL,
                function ($message) {
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess', $message);
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Payload', $message->getPayload());
                    $this->assertEquals([
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
                '{"type":"rpc_notify","uniqid":"abc","payload":["foo","bar"]}' . LineInterface::EOL,
                function ($message) {
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\RpcNotify', $message);
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Payload', $message->getPayload());
                    $this->assertEquals([
                        'type' => 'rpc_notify',
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
                '{"type":"secure","line":"{\"type\":\"rpc\",\"uniqid\":1234567890,\"target\":\"foo\",\"payload\":[\"bar\",\"baz\"]}","signature":"r7TvJ\/AuvAY7dKZ+7wQyI0PdyLivANZzPB35j8Xuyps="}' . LineInterface::EOL,
                function ($message) {
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc', $message);
                    $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Payload', $message->getPayload());
                    $this->assertEquals([
                        'type' => 'rpc',
                        'uniqid' => 1234567890,
                        'payload' => new Payload([
                            'bar',
                            'baz',
                        ]),
                        'target' => 'foo',
                    ], $message->jsonSerialize());

                    return true;
                },
                [
                    'key' => static::KEY,
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerFromLine
     * @param mixed $input
     */
    public function testFromLine($input, callable $tests, array $lineOptions = [])
    {
        $line = Factory::fromLine($input, $lineOptions);
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
}
