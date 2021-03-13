<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\React\ChildProcess\Messenger\OutstandingCall;
use WyriHaximus\TestUtilities\TestCase;

final class OutstandingCallTest extends TestCase
{
    public function testBasic(): void
    {
        $oc = new OutstandingCall('abc', function (): void {}); // phpcs:disabled
        self::assertEquals('abc', $oc->getUniqid());
    }
}
