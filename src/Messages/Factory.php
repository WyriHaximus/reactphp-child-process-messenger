<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Exception;
use Throwable;

use function Safe\json_decode;

final class Factory
{
    private static ?Inflector $inflector = null;

    /**
     * @param array<mixed> $lineOptions
     */
    public static function fromLine(string $line, array $lineOptions): ActionableMessageInterface
    {
        $line   = json_decode($line, true);
        self::$inflector ??= InflectorFactory::create()->build();
        $method = self::$inflector->camelize($line['type']);
        if ($method === 'secure') {
            return static::secureFromLine($line, $lineOptions);
        }

        if ($method === 'message') {
            return static::messageFromLine($line);
        }

        if ($method === 'error') {
            return static::errorFromLine($line);
        }

        if ($method === 'rpc') {
            return static::rpcFromLine($line);
        }

        if ($method === 'rpcError') {
            return static::rpcErrorFromLine($line);
        }

        if ($method === 'rpcSuccess') {
            return static::rpcSuccessFromLine($line);
        }

        /** @phpstan-ignore-next-line */
        throw new Exception('Unknown message type: ' . $line['type']);
    }

    /**
     * @param array<mixed> $payload
     */
    public static function message(array $payload = []): Message
    {
        return new Message(new Payload($payload));
    }

    public static function error(Throwable $payload): Error
    {
        return new Error($payload);
    }

    /**
     * @param array<mixed> $payload
     * @param mixed        $uniqid
     */
    public static function rpc(string $target, array $payload = [], $uniqid = ''): Rpc
    {
        return new Rpc($target, new Payload($payload), $uniqid);
    }

    public static function rpcError(string $uniqid, Throwable $et): RpcError
    {
        return new RpcError($uniqid, $et);
    }

    /**
     * @param array<mixed> $payload
     */
    public static function rpcSuccess(string $uniqid, array $payload = []): RpcSuccess
    {
        return new RpcSuccess($uniqid, new Payload($payload));
    }

    /**
     * @param array<mixed> $line
     * @param array<mixed> $lineOptions
     */
    private static function secureFromLine(array $line, array $lineOptions): ActionableMessageInterface
    {
        return SecureLine::fromLine($line, $lineOptions);
    }

    /**
     * @param array<mixed> $line
     */
    private static function messageFromLine(array $line): Message
    {
        return static::message($line['payload']);
    }

    /**
     * @param array<mixed> $line
     */
    private static function errorFromLine(array $line): Error
    {
        return static::error(LineDecoder::decode($line['payload'])['throwable']);
    }

    /**
     * @param array<mixed> $line
     */
    private static function rpcFromLine(array $line): Rpc
    {
        return static::rpc($line['target'], $line['payload'], $line['uniqid']);
    }

    /**
     * @param array<mixed> $line
     */
    private static function rpcErrorFromLine(array $line): RpcError
    {
        return static::rpcError($line['uniqid'], LineDecoder::decode($line['payload'])['throwable']);
    }

    /**
     * @param  array<mixed> $line
     */
    private static function rpcSuccessFromLine(array $line): RpcSuccess
    {
        return static::rpcSuccess($line['uniqid'], $line['payload']);
    }
}
