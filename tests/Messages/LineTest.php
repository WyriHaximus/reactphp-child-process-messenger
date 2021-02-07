<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\TestUtilities\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

final class LineTest extends TestCase
{
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
                    'u7tfiygouhp089786i7ftuigvyouh9'
                ),
                '{"type":"rpc","uniqid":"u7tfiygouhp089786i7ftuigvyouh9","target":"foo","payload":{"bar":"baz"}}' . LineInterface::EOL,
            ],
            [
                new Rpc(
                    'foo',
                    new Payload([
                        'bar',
                        'baz',
                    ]),
                    'oisuerhfuahugoireu'
                ),
                '{"type":"rpc","uniqid":"oisuerhfuahugoireu","target":"foo","payload":["bar","baz"]}' . LineInterface::EOL,
            ],
        ];
    }

    /**
     * @param mixed $output
     *
     * @dataProvider providerBasic
     */
    public function testBasic(ActionableMessageInterface $input, $output): void
    {
        $line = new Line($input, []);
        self::assertSame($input, $line->getPayload());
        self::assertEquals($output, (string)$line);
    }
}
