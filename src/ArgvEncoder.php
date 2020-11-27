<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use function base64_encode;
use function Safe\base64_decode;
use function serialize;
use function strlen;
use function unserialize;

final class ArgvEncoder
{
    /**
     * @param array<string> $argv
     */
    public static function encode(array $argv): string
    {
        return base64_encode(serialize($argv));
    }

    /**
     * @return array<mixed>
     */
    public static function decode(string $argv): array
    {
        if (strlen($argv) === 0) {
            return [];
        }

        return unserialize(base64_decode($argv, true));
    }
}
