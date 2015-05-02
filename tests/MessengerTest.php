<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Phake;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

class MessengerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var React\ChildProcess\Process
     */
    protected $process;

    public function setUp()
    {
        $this->loop = Phake::mock('React\EventLoop\LoopInterface');
        $this->process = Phake::mock('React\ChildProcess\Process');
        $this->process->stdin = Phake::mock('React\Stream\Stream');
        $this->process->stdout = Phake::mock('React\Stream\Stream');
        $this->process->stderr = Phake::mock('React\Stream\Stream');
    }

    public function tearDown()
    {
        unset($this->process, $this->loop);
    }

    public function testStart()
    {
        (new Messenger($this->process))->start($this->loop);
        Phake::inOrder(
            Phake::verify($this->process)->start($this->loop, Messenger::INTERVAL),
            Phake::verify($this->process->stdout)->on('data', $this->isType('callable')),
            Phake::verify($this->process->stderr)->on('data', $this->isType('callable'))
        );
    }
}
