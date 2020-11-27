<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Cake\Utility\Hash;
use Throwable;

use function WyriHaximus\throwable_encode;

final class LineEncoder
{
    public const META_KEY       = 'a';
    public const TYPE_KEY       = 'b';
    public const VALUE_KEY      = 'c';
    public const THROWABLES_KEY = 'd';
    public const TYPE_THROWABLE = 'e';
    public const TYPE_ARRAY     = 'f';

    /**
     * @param array<mixed>|Throwable $line
     *
     * @return array<mixed>
     */
    public static function encode($line): array
    {
        if ($line instanceof Throwable) {
            return [
                self::META_KEY => [
                    self::TYPE_KEY => self::TYPE_THROWABLE,
                ],
                self::VALUE_KEY => throwable_encode($line),
            ];
        }

        $throwables = [];
        $line       = Hash::flatten($line);
        foreach ($line as $key => $value) {
            if (! ($value instanceof Throwable)) {
                continue;
            }

            $throwables[] = $key;
            $line[$key]   = throwable_encode($value);
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
