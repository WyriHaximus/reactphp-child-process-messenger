<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use ReflectionClass;

final class StaticConfig
{
    public static function shouldListFileDescriptors(): bool
    {
        static $should = null;
        if ($should !== null) {
            return $should;
        }

        $arguments = (new ReflectionClass(Process::class))->getConstructor()->getParameters(); /** @phpstan-ignore-line */
        if (! isset($arguments[3])) { /** @phpstan-ignore-line */
            return $should = false;
        }

        return $should = ($arguments[3]->getName() === 'fds');
    }
}
