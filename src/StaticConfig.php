<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

final class StaticConfig
{
    public static function shouldListFileDescriptors()
    {
        static $should = null;
        if ($should !== null) {
            return $should;
        }

        $arguments = (new \ReflectionClass('React\ChildProcess\Process'))->getConstructor()->getParameters();
        if (!isset($arguments[3])) {
            return $should = false;
        }

        return $should = ($arguments[3]->getName() === 'fds');
    }
}
