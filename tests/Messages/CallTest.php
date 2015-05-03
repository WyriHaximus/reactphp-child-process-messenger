<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;

class CallTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $target = 'target';
        $message = new Payload([]);
        $vo = new Call($target, $message);
        $this->assertEquals($target, $vo->getTarget());
        $this->assertEquals($message, $vo->getMessage());
    }
}
