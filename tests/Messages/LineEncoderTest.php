<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Exception;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineEncoder;
use WyriHaximus\TestUtilities\TestCase;

use function WyriHaximus\throwable_encode;

final class LineEncoderTest extends TestCase
{
    /**
     * @return iterable<array<Exception|array<mixed>>>
     */
    public function provideLines(): iterable
    {
        $lines = [];

        $exception          = new Exception('whoops');
        $lines['throwable'] = [
            $exception,
            [
                LineEncoder::META_KEY => [
                    LineEncoder::TYPE_KEY => LineEncoder::TYPE_THROWABLE,
                ],
                LineEncoder::VALUE_KEY => throwable_encode($exception),
            ],
        ];

        $lines['simple'] = [
            ['foo' => 'bar'],
            [
                LineEncoder::META_KEY => [
                    LineEncoder::TYPE_KEY => LineEncoder::TYPE_ARRAY,
                    LineEncoder::THROWABLES_KEY => [],
                ],
                LineEncoder::VALUE_KEY => ['foo' => 'bar'],
            ],
        ];

        $lines['complex'] = [
            [
                'foo' => 'bar',
                'bar' => [
                    'level1' => [
                        $exception,
                        ['level2' => $exception],
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
                    'bar.level1.0' => throwable_encode($exception),
                    'bar.level1.1.level2' => throwable_encode($exception),
                    'boom' => throwable_encode($exception),
                ],
            ],
        ];

        return $lines;
    }

    /**
     * @param mixed        $in
     * @param array<mixed> $out
     *
     * @dataProvider provideLines
     */
    public function testLines($in, array $out): void
    {
        $encodedLine = LineEncoder::encode($in);
        self::assertSame($out, $encodedLine);
    }
}
