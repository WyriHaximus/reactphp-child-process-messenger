<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\TestUtilities\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\OutstandingCall;

final class OutstandingCallTest extends TestCase
{
    public function testBasic(): void
    {
        $oc = new OutstandingCall('abc', static function (): void {
        });
        self::assertEquals('abc', $oc->getUniqid());
    }
}
