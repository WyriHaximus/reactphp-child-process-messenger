<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\TestUtilities\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineDecoder;

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
        $line = LineDecoder::decode($out);
        self::assertEquals($in, $line);
    }
}
