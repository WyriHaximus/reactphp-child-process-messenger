<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use WyriHaximus\TestUtilities\TestCase;
use Prophecy\Argument;
use WyriHaximus\React\ChildProcess\Messenger\Process;

final class ProcessTest extends TestCase
{
    public function testProcess(): void
    {
        $loop      = $this->prophesize('React\EventLoop\LoopInterface');
        $messenger = $this->prophesize('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        $messenger->registerRpc(Argument::type('string'), Argument::type('callable'))->shouldBeCalled();

        Process::create($loop->reveal(), $messenger->reveal());
    }
}
