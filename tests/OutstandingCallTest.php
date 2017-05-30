<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\OutstandingCall;

class OutstandingCallTest extends TestCase
{
    public function testBasic()
    {
        $oc = new OutstandingCall('abc', function () {
        });
        $this->assertEquals('abc', $oc->getUniqid());
        $this->assertInstanceOf('React\Promise\Deferred', $oc->getDeferred());
    }
}
