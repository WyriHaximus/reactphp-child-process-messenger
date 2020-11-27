<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

interface LineInterface
{
    public const EOL = "\r\n";

    /**
     * @param array<mixed> $options
     */
    public function __construct(ActionableMessageInterface $line, array $options);

    public function __toString(): string;
}
