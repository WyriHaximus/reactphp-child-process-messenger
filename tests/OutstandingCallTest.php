<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\OutstandingCall;

class OutstandingCallTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $oc = new OutstandingCall('abc');
        $this->assertEquals('abc', $oc->getUniqid());
        $this->assertInstanceOf(Deferred::class, $oc->getDeferred());
    }
}
