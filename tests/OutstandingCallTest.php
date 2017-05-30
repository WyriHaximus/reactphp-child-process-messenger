<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\React\ChildProcess\Messenger\OutstandingCall;

class OutstandingCallTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $oc = new OutstandingCall('abc', function () {
        });
        $this->assertEquals('abc', $oc->getUniqid());
        $this->assertInstanceOf('React\Promise\Deferred', $oc->getDeferred());
    }
}
