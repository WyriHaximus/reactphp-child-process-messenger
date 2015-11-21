<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\React\ChildProcess\Messenger\OutstandingCalls;

class OutstandingCallsTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $oc = new OutstandingCalls();
        $call = $oc->newCall();
        $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\OutstandingCall', $call);
        $this->assertEquals($call, $oc->getCall($call->getUniqid()));
    }
}
