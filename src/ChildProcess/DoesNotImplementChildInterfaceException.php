<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\ChildProcess;

use Exception;

/**
 * @psalm-suppress MissingConstructor
 */
final class DoesNotImplementChildInterfaceException extends Exception
{
    private string $class;

    public static function create(string $class): self
    {
        $self        = new self('Given class doesn\'t implement ChildInterface');
        $self->class = $class;

        return $self;
    }

    public function class(): string
    {
        return $this->class;
    }
}
