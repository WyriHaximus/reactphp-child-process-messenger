<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\ChildProcess\Messenger\ChildProcess;

use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use WyriHaximus\React\ChildProcess\Messenger\ChildProcess\Options;
use WyriHaximus\React\ChildProcess\Messenger\ChildProcess\Process;
use WyriHaximus\React\ChildProcess\Messenger\Factory as MessengerFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use WyriHaximus\TestUtilities\TestCase;

final class ProcessTest extends TestCase
{
    public function testProcess(): void
    {
        $loop       = $this->prophesize(LoopInterface::class);
        $connection = $this->prophesize(ConnectionInterface::class);
        $messenger  = new Messenger($connection->reveal(), new Options('abc', '1208301289', 987609));

        Process::create($loop->reveal(), $messenger);

        self::assertTrue($messenger->hasRpc(MessengerFactory::PROCESS_REGISTER));
    }
}
