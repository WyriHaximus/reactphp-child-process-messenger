<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Exception;
use Throwable;

use function get_class;
use function Safe\json_decode;
use function method_exists;

final class  Factory
{
    /**
     * @param array<mixed> $lineOptions
     */
    public static function fromLine(string $line, array $lineOptions): ActionableMessageInterface
    {
        $line   = json_decode($line, true);
        $method = InflectorFactory::create()->build()->camelize($line['type']) . 'FromLine';
        if (method_exists(get_class(new static()), $method) && $method !== 'FromLine') {
            return static::$method($line, $lineOptions); /** @phpstan-ignore-line  */
        }

        throw new Exception('Unknown message type: ' . $line['type']); /** @phpstan-ignore-line  */
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
     * @param array<mixed> $payload
     */
    public static function rpcNotify(string $uniqid, array $payload = []): RpcNotify
    {
        return new RpcNotify($uniqid, new Payload($payload));
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

    /**
     * @param  array<mixed> $line
     */
    private static function rpcNotifyFromLine(array $line): RpcNotify
    {
        return static::rpcNotify($line['uniqid'], $line['payload']);
    }
}
