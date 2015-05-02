<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\RpcCall;

class RpcCallTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $target = 'target';
        $message = [];
        $vo = new RpcCall($target, $message);
        $this->assertEquals($target, $vo->getTarget());
        $this->assertEquals($message, $vo->getMessage());
    }
}
