<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Clue\React\Block;
use Phake;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\Server;
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
        $this->process = Phake::mock('React\ChildProcess\Process');
    }

    public function tearDown()
    {
        unset($this->process, $this->loop);
    }

    public function testChild()
    {
        $server = new Server(0, $this->loop);
        $messenger = Block\await(Factory::child($this->loop, [
            'address' => $server->getAddress(),
        ]), $this->loop);
        $this->assertInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messenger', $messenger);
        $messenger->callRpc('wyrihaximus.react.child-process.messenger.terminate', new Payload([]));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Given class doesn't implement ChildInterface
     */
    public function testParentFromClassException()
    {
        Factory::parent('stdClass', Phake::mock('React\EventLoop\LoopInterface'));
    }

    public function testParentFromClassActualRun()
    {
        $ranMessengerCreateCallback = false;
        $ranChildProcessCallback = false;
        $loop = \React\EventLoop\Factory::create();
        Factory::parent('WyriHaximus\React\ChildProcess\Messenger\ReturnChild', $loop)->then(function (Messenger $messenger) use (&$ranMessengerCreateCallback, &$ranChildProcessCallback) {
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
