<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\Messages;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Call;

class CallTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $target = 'target';
        $message = [];
        $vo = new Call($target, $message);
        $this->assertEquals($target, $vo->getTarget());
        $this->assertEquals($message, $vo->getMessage());
    }
}
