<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory as EventLoopFactory;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class FactoryTest extends TestCase
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var \React\ChildProcess\Process
     */
    protected $process;

    public function setUp()
    {
        $this->loop = EventLoopFactory::create();
        $this->process = $this->prophesize('React\ChildProcess\Process')->reveal();
    }

    public function tearDown()
    {
        unset($this->process, $this->loop);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Given class doesn't implement ChildInterface
     */
    public function testParentFromClassException()
    {
        Factory::parentFromClass('stdClass', $this->prophesize('React\EventLoop\LoopInterface')->reveal());
    }

    public function testParentFromClassActualRun()
    {
        $ranMessengerCreateCallback = false;
        $ranChildProcessCallback = false;
        $loop = \React\EventLoop\Factory::create();
        Factory::parentFromClass('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)->then(function (Messenger $messenger) use (&$ranMessengerCreateCallback, &$ranChildProcessCallback) {
            $ranMessengerCreateCallback = true;
            $messenger->rpc(\WyriHaximus\React\ChildProcess\Messenger\Messages\Factory::rpc('return', [
                'foo' => 'bar',
            ]))->then(function (Payload $payload) use (&$ranChildProcessCallback, $messenger) {
                $this->assertSame([
                    'foo' => 'bar',
                ], $payload->getPayload());
                $ranChildProcessCallback = true;
                $messenger->softTerminate();
            });
        });
        $loop->run();
        $this->assertTrue($ranMessengerCreateCallback);
        $this->assertTrue($ranChildProcessCallback);
    }
}
