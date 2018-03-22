<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineEncoder;

class LineEncoderTest extends TestCase
{
    public function provideLines()
    {
        $lines = [];

        $exception = new \Exception('whoops');
        $lines[] = [
            $exception,
            [
                LineEncoder::META_KEY => [
                    LineEncoder::TYPE_KEY => LineEncoder::TYPE_THROWABLE,
                ],
                LineEncoder::VALUE_KEY => \WyriHaximus\throwable_encode($exception),
            ],
        ];

        $lines[] = [
            [
                'foo' => 'bar',
            ],
            [
                LineEncoder::META_KEY => [
                    LineEncoder::TYPE_KEY => LineEncoder::TYPE_ARRAY,
                    LineEncoder::THROWABLES_KEY => [],
                ],
                LineEncoder::VALUE_KEY => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $lines[] = [
            [
                'foo' => 'bar',
                'bar' => [
                    'level1' => [
                        $exception,
                        [
                            'level2' => $exception,
                        ],
                    ],
                ],
                'boom' => $exception,
            ],
            [
                LineEncoder::META_KEY => [
                    LineEncoder::TYPE_KEY => LineEncoder::TYPE_ARRAY,
                    LineEncoder::THROWABLES_KEY => [
                        'bar.level1.0',
                        'bar.level1.1.level2',
                        'boom',
                    ],
                ],
                LineEncoder::VALUE_KEY => [
                    'foo' => 'bar',
                    'bar.level1.0' => \WyriHaximus\throwable_encode($exception),
                    'bar.level1.1.level2' => \WyriHaximus\throwable_encode($exception),
                    'boom' => \WyriHaximus\throwable_encode($exception),
                ],
            ],
        ];

        return $lines;
    }

    /**
     * @param mixed $in
     * @param array $out
     * @dataProvider provideLines
     */
    public function testLines($in, array $out)
    {
        $encodedLine = LineEncoder::encode($in);
        $this->assertSame($out, $encodedLine);
    }
}
