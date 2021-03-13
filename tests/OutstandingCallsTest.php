<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\React\ChildProcess\Messenger\OutstandingCalls;
use WyriHaximus\TestUtilities\TestCase;

final class OutstandingCallsTest extends TestCase
{
    public function testBasic(): void
    {
        $oc   = new OutstandingCalls();
        $call = $oc->newCall(function (): void {}); // phpcs:disabled
        self::assertEquals([$call], $oc->getCalls());
        self::assertEquals($call, $oc->getCall($call->getUniqid()));
    }
}
