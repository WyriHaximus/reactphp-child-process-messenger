<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Cake\Utility\Hash;
use Exception;
use Throwable;

final class LineEncoder
{
    const META_KEY = 'a';
    const TYPE_KEY = 'b';
    const VALUE_KEY = 'c';
    const THROWABLES_KEY = 'd';
    const TYPE_THROWABLE = 'e';
    const TYPE_ARRAY = 'f';

    public static function encode($line)
    {
        if ($line instanceof Exception || $line instanceof Throwable) {
            return [
                self::META_KEY => [
                    self::TYPE_KEY => self::TYPE_THROWABLE,
                ],
                self::VALUE_KEY => \WyriHaximus\throwable_encode($line),
            ];
        }

        $throwables = [];
        $line = Hash::flatten($line);
        foreach ($line as $key => $value) {
            if (!($value instanceof Exception) && !($value instanceof Throwable)) {
                continue;
            }

            $throwables[] = $key;
            $line[$key] = \WyriHaximus\throwable_encode($value);
        }

        return [
            self::META_KEY => [
                self::TYPE_KEY => self::TYPE_ARRAY,
                self::THROWABLES_KEY => $throwables,
            ],
            self::VALUE_KEY => $line,
        ];
    }
}
