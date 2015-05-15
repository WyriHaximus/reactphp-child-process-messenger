<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;

class LineTest extends \PHPUnit_Framework_TestCase
{
    public function providerBasic()
    {
        yield [
            [],
            '[]' . PHP_EOL,
        ];

        yield [
            [
                'type' => 'rpc',
                'uniqid' => 1234567890,
                'target' => 'foo',
                'payload' => [
                    'bar' => 'baz'
                ],
            ],
            '{"type":"rpc","uniqid":1234567890,"target":"foo","payload":{"bar":"baz"}}' . PHP_EOL,
        ];

        yield [
            [
                'type' => 'rpc',
                'uniqid' => 1234567890,
                'target' => 'foo',
                'payload' => [
                    'bar',
                    'baz',
                ],
            ],
            '{"type":"rpc","uniqid":1234567890,"target":"foo","payload":["bar","baz"]}' . PHP_EOL,
        ];
    }

    /**
     * @dataProvider providerBasic
     */
    public function testBasic(array $input, $output)
    {
        $this->assertEquals($output, (string)new Line($input));
    }
}
