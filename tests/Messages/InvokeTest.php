<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use React\EventLoop\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Invoke;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class InvokeTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $loop = Factory::create();
        $array = [];
        $payload = new Payload($array);
        $vo = new Invoke($loop, $payload);
        $this->assertEquals($loop, $vo->getLoop());
        $this->assertEquals($array, $vo->getPayload());
        $this->assertInstanceOf('React\Promise\Deferred', $vo->getDeferred());
        $this->assertInstanceOf('React\Promise\PromiseInterface', $vo->getPromise());
    }
}
