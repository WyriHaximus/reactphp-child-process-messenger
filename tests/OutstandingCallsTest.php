<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use PHPUnit\Framework\TestCase;
use WyriHaximus\React\ChildProcess\Messenger\OutstandingCalls;

class OutstandingCallsTest extends TestCase
{
    public function testBasic()
    {
        $oc = new OutstandingCalls();
        $call = $oc->newCall();
        $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\OutstandingCall', $call);
        $this->assertEquals($call, $oc->getCall($call->getUniqid()));
    }
}
