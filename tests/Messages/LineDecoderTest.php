<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use Throwable;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineDecoder;
use WyriHaximus\TestUtilities\TestCase;

final class LineDecoderTest extends TestCase
{
    /**
     * @param mixed        $in
     * @param array<mixed> $out
     *
     * @dataProvider \WyriHaximus\React\Tests\ChildProcess\Messenger\Messages\LineEncoderTest::provideLines
     */
    public function testLines($in, array $out): void
    {
        if ($in instanceof Throwable) {
            $in = ['throwable' => $in];
        }

        $line = LineDecoder::decode($out);
        self::assertEquals($in, $line);
    }
}
