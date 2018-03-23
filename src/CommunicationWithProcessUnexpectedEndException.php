<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use Exception;

final class CommunicationWithProcessUnexpectedEndException extends Exception
{
    public function __construct()
    {
        parent::__construct('Communication with process stopped unexpectedly');
    }
}
