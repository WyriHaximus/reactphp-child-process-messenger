<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\LineInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

interface MessengerInterface
{
    /**
     * @param string   $target
     * @param callable $listener
     */
    public function registerRpc($target, callable $listener);

    /**
     * @param string $target
     */
    public function deregisterRpc($target);

    /**
     * @param  string $target
     * @return bool
     */
    public function hasRpc($target);

    /**
     * @param $target
     * @param $payload
     * @return React\Promise\PromiseInterface
     */
    public function callRpc($target, $payload);

    /**
     * @param Message $message
     */
    public function message(Message $message);

    /**
     * @param Error $error
     */
    public function error(Error $error);

    /**
     * @param  string          $uniqid
     * @return OutstandingCall
     */
    public function getOutstandingCall($uniqid);

    /**
     * @param  Rpc                    $rpc
     * @return \React\Promise\Promise
     */
    public function rpc(Rpc $rpc);

    /**
     * @param  ActionableMessageInterface $line
     * @return LineInterface
     */
    public function createLine(ActionableMessageInterface $line);

    /**
     * @return \React\Promise\Promise
     */
    public function softTerminate();

    /**
     * @param string $line
     */
    public function write($line);
}
