<?php

namespace WyriHaximus\React\Tests\ChildProcess\Messenger;

use Phake;
use React\EventLoop\Factory as EventLoopFactory;
use React\Promise\Deferred;
use WyriHaximus\React\ChildProcess\Messenger\Factory;

class MessengerTest extends \PHPUnit_Framework_TestCase
{

    public function testSetAndHasRpc()
    {
        $loop = EventLoopFactory::create();
        $messenger = Factory::child($loop);

        $payload = [
            'a',
            'b',
            'c',
        ];
        $deferred = new Deferred();
        $callableFired = false;
        $callable = function (array $passedPayload, Deferred $passedDeferred) use (&$callableFired, $payload, $deferred) {
            $this->assertEquals($payload, $passedPayload);
            $this->assertEquals($deferred, $passedDeferred);
            $callableFired = true;
        };

        $messenger->registerRpc('test', $callable);
        $this->assertFalse($messenger->hasRpc('tset'));
        $this->assertTrue($messenger->hasRpc('test'));

        $messenger->callRpc('test', $payload, $deferred);

        $this->assertTrue($callableFired);
    }
}
