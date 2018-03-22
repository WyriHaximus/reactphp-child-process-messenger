<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Cake\Utility\Hash;

final class LineDecoder
{
    public static function decode(array $line)
    {
        if ($line[LineEncoder::META_KEY][LineEncoder::TYPE_KEY] === LineEncoder::TYPE_THROWABLE) {
            return \WyriHaximus\throwable_decode($line[LineEncoder::VALUE_KEY]);
        }

        foreach ($line[LineEncoder::META_KEY][LineEncoder::THROWABLES_KEY] as $throwableKey) {
            $line[LineEncoder::VALUE_KEY][$throwableKey] = \WyriHaximus\throwable_decode($line[LineEncoder::VALUE_KEY][$throwableKey]);
        }

        return Hash::expand($line[LineEncoder::VALUE_KEY]);
    }
}
