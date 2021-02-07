<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\TestUtilities\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\OutstandingCalls;

final class OutstandingCallsTest extends TestCase
{
    public function testBasic(): void
    {
        $oc   = new OutstandingCalls();
        $call = $oc->newCall(static function (): void {
        });
        self::assertEquals([$call], $oc->getCalls());
        self::assertEquals($call, $oc->getCall($call->getUniqid()));
    }
}
