<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcError;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcNotify;
use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcSuccess;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    const KEY = 'abc';

    public function providerFromLine()
    {
        yield [
            '{"type":"message","payload":["foo","bar"]}' . LineInterface::EOL,
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
        yield [
            '{"type":"error","payload":["foo","bar"]}' . LineInterface::EOL,
            function ($message) {
                $this->assertInstanceOf(Error::class, $message);
                $this->assertInstanceOf(Payload::class, $message->getPayload());
                $this->assertSame([
                    'foo',
                    'bar',
                ], $message->getPayload()->getPayload());
                return true;
            },
        ];
        yield [
            '{"type":"rpc","uniqid":"abc","target":"foo","payload":["foo","bar"]}' . LineInterface::EOL,
            function ($message) {
                $this->assertInstanceOf(Rpc::class, $message);
                $this->assertInstanceOf(Payload::class, $message->getPayload());
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
        ];
        yield [
            '{"type":"rpc_error","uniqid":"abc","payload":["foo","bar"]}' . LineInterface::EOL,
            function ($message) {
                $this->assertInstanceOf(RpcError::class, $message);
                $this->assertInstanceOf(Payload::class, $message->getPayload());
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
        ];
        yield [
            '{"type":"rpc_success","uniqid":"abc","payload":["foo","bar"]}' . LineInterface::EOL,
            function ($message) {
                $this->assertInstanceOf(RpcSuccess::class, $message);
                $this->assertInstanceOf(Payload::class, $message->getPayload());
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
        ];
        yield [
            '{"type":"rpc_notify","uniqid":"abc","payload":["foo","bar"]}' . LineInterface::EOL,
            function ($message) {
                $this->assertInstanceOf(RpcNotify::class, $message);
                $this->assertInstanceOf(Payload::class, $message->getPayload());
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
        ];
        yield [
            '{"type":"secure","line":"{\"type\":\"rpc\",\"uniqid\":1234567890,\"target\":\"foo\",\"payload\":[\"bar\",\"baz\"]}","signature":"r7TvJ\/AuvAY7dKZ+7wQyI0PdyLivANZzPB35j8Xuyps="}' . LineInterface::EOL,
            function ($message) {
                $this->assertInstanceOf(Rpc::class, $message);
                $this->assertInstanceOf(Payload::class, $message->getPayload());
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
        ];
    }

    /**
     * @dataProvider providerFromLine
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
