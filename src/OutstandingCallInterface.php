<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use React\Promise\Deferred;

interface OutstandingCallInterface
{
    /**
     * @return mixed
     */
    public function getUniqid();

    public function getDeferred(): Deferred;

    /**
     * @param mixed $value
     */
    public function resolve($value): void;

    /**
     * @param mixed $value
     */
    public function reject($value): void;
}
