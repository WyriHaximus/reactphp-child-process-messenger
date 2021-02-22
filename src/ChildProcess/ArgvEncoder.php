<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\ChildProcess;

use function base64_encode;
use function Safe\base64_decode;
use function Safe\json_decode;
use function Safe\json_encode;

final class ArgvEncoder
{
    public static function encode(Options $options): string
    {
        return base64_encode(json_encode($options->toArray()));
    }

    public static function decode(string $argv): Options
    {
        return new Options(...json_decode(base64_decode($argv, true)));
    }
}
