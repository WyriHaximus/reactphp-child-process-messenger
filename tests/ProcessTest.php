<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Phake;
use WyriHaximus\React\ChildProcess\Messenger\Factory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Process;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        Phake::when($loop)->futureTick($this->isType('callable'))->thenGetReturnByLambda(function ($callable) {
            $callable();
        });

        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->registerRpc(Factory::PROCESS_REGISTER, $this->isType('callable'))->thenGetReturnByLambda(function ($rpcName, $callable) {
            $callable(new Payload([
                'className' => 'WyriHaximus\React\ChildProcess\Messenger\ReturnChild',
            ]));
        });
        Process::create($loop, $messenger);

        Phake::verify($messenger)->registerRpc('return', $this->isType('callable'));
        Phake::verify($messenger)->deregisterRpc(Factory::PROCESS_REGISTER);
    }

    public function testProcessException()
    {
        $loop = Phake::mock('React\EventLoop\LoopInterface');
        Phake::when($loop)->addTimer(1, $this->isType('callable'))->thenGetReturnByLambda(function ($int, $callable) {
            $callable();
        });

        $messenger = Phake::mock('WyriHaximus\React\ChildProcess\Messenger\Messenger');
        Phake::when($messenger)->registerRpc(Factory::PROCESS_REGISTER, $this->isType('callable'))->thenGetReturnByLambda(function ($rpcName, $callable) {
            $callable(new Payload([
                'className' => 'stdClass',
            ]));
        });
        Phake::when($messenger)->error($this->isInstanceOf('WyriHaximus\React\ChildProcess\Messenger\Messages\Error'))->thenGetReturnByLambda(function (Error $error) {
            $this->assertSame([
                'message' => 'Given class doesn\'t implement ChildInterface',
                'code' => 0,
                'line' => 59,
                'file' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Process.php',
            ], $error->getPayload()->getPayload());
        });
        Process::create($loop, $messenger);
    }
}
