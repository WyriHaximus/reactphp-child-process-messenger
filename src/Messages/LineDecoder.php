<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Cake\Utility\Hash;
use Doctrine\Instantiator\Exception\ExceptionInterface;
use ReflectionException;

use function WyriHaximus\throwable_decode;

final class LineDecoder
{
    /**
     * @param array<mixed> $line
     *
     * @return array<mixed>
     *
     * @throws ExceptionInterface
     * @throws ReflectionException
     */
    public static function decode(array $line): array
    {
        if ($line[LineEncoder::META_KEY][LineEncoder::TYPE_KEY] === LineEncoder::TYPE_THROWABLE) {
            return ['throwable' => throwable_decode($line[LineEncoder::VALUE_KEY])];
        }

        foreach ($line[LineEncoder::META_KEY][LineEncoder::THROWABLES_KEY] as $throwableKey) {
            $line[LineEncoder::VALUE_KEY][$throwableKey] = throwable_decode($line[LineEncoder::VALUE_KEY][$throwableKey]);
        }

        return Hash::expand($line[LineEncoder::VALUE_KEY]);
    }
}
