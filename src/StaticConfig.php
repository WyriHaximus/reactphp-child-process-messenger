<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use ReflectionClass;
use WyriHaximus\React\ChildProcess\Messenger\ChildProcess\Process;

use const WyriHaximus\Constants\Boolean\FALSE_;

final class StaticConfig
{
    private const EXPECTED_INDEX = 3;

    public static function shouldListFileDescriptors(): bool
    {
        static $should = null;
        if ($should !== null) {
            return $should;
        }

        /**
         * @psalm-suppress PossiblyNullReference
         */
        $arguments = (new ReflectionClass(Process::class))->getConstructor()->getParameters(); /** @phpstan-ignore-line */
        if (! isset($arguments[self::EXPECTED_INDEX])) { /** @phpstan-ignore-line */
            return $should = FALSE_;
        }

        return $should = ($arguments[self::EXPECTED_INDEX]->getName() === 'fds');
    }
}
