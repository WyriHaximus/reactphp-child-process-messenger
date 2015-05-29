<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

class LineTest extends \PHPUnit_Framework_TestCase
{
    public function providerBasic()
    {
        yield [
            new Rpc(
                'foo',
                new Payload([
                    'bar' => 'baz'
                ]),
                1234567890
            ),
            '{"type":"rpc","uniqid":1234567890,"target":"foo","payload":{"bar":"baz"}}' . LineInterface::EOL,
        ];

        yield [
            new Rpc(
                'foo',
                new Payload([
                    'bar',
                    'baz',
                ]),
                1234567890
            ),
            '{"type":"rpc","uniqid":1234567890,"target":"foo","payload":["bar","baz"]}' . LineInterface::EOL,
        ];
    }

    /**
     * @dataProvider providerBasic
     */
    public function testBasic(\JsonSerializable $input, $output)
    {
        $this->assertEquals($output, (string)new Line($input, []));
    }
}
