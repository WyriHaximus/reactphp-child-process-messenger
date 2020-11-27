<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use Exception;

final class ProcessUnexpectedEndException extends Exception
{
    public function __construct(int $exitCode)
    {
        parent::__construct('Process stopped unexpectedly', $exitCode);
    }
}
