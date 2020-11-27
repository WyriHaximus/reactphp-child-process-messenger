<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use JsonSerializable;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;

interface ActionableMessageInterface extends JsonSerializable
{
    public function handle(Messenger $bindTo, string $source): void;
}
