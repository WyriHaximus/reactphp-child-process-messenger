<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use WyriHaximus\React\ChildProcess\Messenger\Process;

class ProcessTest extends TestCase
{
    public function testProcess()
    {
        $loop = $this->prophesize('React\EventLoop\LoopInterface');
        $messenger = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        $messenger->registerRpc(Argument::type('string'), Argument::type('callable'))->shouldBeCalled();

        Process::create($loop->reveal(), $messenger->reveal());
    }
}
