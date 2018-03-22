<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineDecoder;

class LineDecoderTest extends TestCase
{
    /**
     * @param mixed $in
     * @param array $out
     * @dataProvider \WyriHaximus\React\Tests\ChildProcess\Messenger\Messages\LineEncoderTest::provideLines
     */
    public function testLines($in, array $out)
    {
        $line = LineDecoder::decode($out);
        $this->assertEquals($in, $line);
    }
}
