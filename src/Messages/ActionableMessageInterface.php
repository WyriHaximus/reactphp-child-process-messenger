<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use JsonSerializable;
use WyriHaximus\React\ChildProcess\Messenger\MessengerInterface;

interface ActionableMessageInterface extends JsonSerializable
{
    public function handle(MessengerInterface $bindTo, string $source): void;
}
