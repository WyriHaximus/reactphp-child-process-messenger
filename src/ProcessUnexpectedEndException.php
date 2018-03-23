<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use Exception;

final class ProcessUnexpectedEndException extends Exception
{
    /**
     * @param int $exitCode
     */
    public function __construct($exitCode)
    {
        parent::__construct('Process stopped unexpectedly', $exitCode);
    }
}
