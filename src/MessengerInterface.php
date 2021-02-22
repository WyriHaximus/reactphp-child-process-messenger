<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\ActionableMessageInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Error;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Message;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;

interface MessengerInterface
{
    public function registerRpc(string $target, callable $listener): void;

    public function deregisterRpc(string $target): void;

    public function hasRpc(string $target): bool;

    public function callRpc(string $target, Payload $payload): PromiseInterface;

    public function message(Message $message): void;

    public function error(Error $error): void;

    public function getOutstandingCall(string $uniqid): OutstandingCallInterface;

    public function rpc(Rpc $rpc): PromiseInterface;

    public function createLine(ActionableMessageInterface $line): string;

    public function softTerminate(): PromiseInterface;

    public function write(string $line): void;

//    /**
//     * @internal
//     */
//    public function crashed(int $exitCode): void
//    {
//        $this->emit('error', [new ProcessUnexpectedEndException($exitCode), $this]);
//    }
}
