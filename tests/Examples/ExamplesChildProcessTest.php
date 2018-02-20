<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Examples;

use PHPUnit\Framework\TestCase;

final class ExamplesChildProcessTest extends TestCase
{
    public function providePrimesAndNonPrimes()
    {
        return [
            [
                1,
                false,
            ],
            [
                2,
                true,
            ],
            [
                3,
                true,
            ],
            [
                4,
                false,
            ],
            [
                5,
                true,
            ],
            [
                6,
                false,
            ],
            [
                7,
                true,
            ],
            [
                8,
                false,
            ],
            [
                9,
                false,
            ],
            [
                10,
                false,
            ],
            [
                11,
                true,
            ],
            [
                12,
                false,
            ],
            [
                13,
                true,
            ],
            [
                14,
                false,
            ],
            [
                15,
                false,
            ],
            [
                16,
                false,
            ],
            [
                17,
                true,
            ],
            [
                18,
                false,
            ],
            [
                19,
                true,
            ],
            [
                20,
                false,
            ],
            [
                21,
                false,
            ],
            [
                22,
                false,
            ],
            [
                23,
                true,
            ],
            [
                24,
                false,
            ],
            [
                25,
                false,
            ],
        ];
    }

    /**
     * @dataProvider providePrimesAndNonPrimes
     */
    public function testIsPrime($number, $isPrime)
    {
        self::assertSame($isPrime, \ExamplesChildProcess::isPrime($number));
    }
}