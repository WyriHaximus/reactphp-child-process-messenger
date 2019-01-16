<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Doctrine\Common\Inflector\Inflector;
use Exception;
use Throwable;

class Factory
{
    /**
     * @param string $line
     *
     * @throws \Exception
     * @return mixed
     *
     */
    public static function fromLine($line, array $lineOptions)
    {
        $line = \json_decode($line, true);
        $method = Inflector::camelize($line['type']) . 'FromLine';
        if (\method_exists(\get_class(new static()), $method) && $method !== 'FromLine') {
            return static::$method($line, $lineOptions);
        }

        throw new \Exception('Unknown message type: ' . $line['type']);
    }

    /**
     * @param array $payload
     *
     * @return Message
     */
    public static function message(array $payload = [])
    {
        return new Message(new Payload($payload));
    }

    /**
     * @param array $payload
     *
     * @return Exception|Throwable
     */
    public static function error($payload)
    {
        return new Error($payload);
    }

    /**
     * @param string $target
     * @param array  $payload
     * @param mixed  $uniqid
     *
     * @return Rpc
     */
    public static function rpc($target, array $payload = [], $uniqid = '')
    {
        return new Rpc($target, new Payload($payload), $uniqid);
    }

    /**
     * @param mixed               $uniqid
     * @param Exception|Throwable $payload
     * @param mixed               $et
     *
     * @return Rpc
     */
    public static function rpcError($uniqid, $et)
    {
        return new RpcError($uniqid, $et);
    }

    /**
     * @param string $uniqid
     * @param array  $payload
     *
     * @return RpcSuccess
     */
    public static function rpcSuccess($uniqid, array $payload = [])
    {
        return new RpcSuccess($uniqid, new Payload($payload));
    }

    /**
     * @param string $uniqid
     * @param array  $payload
     *
     * @return RpcNotify
     */
    public static function rpcNotify($uniqid, array $payload = [])
    {
        return new RpcNotify($uniqid, new Payload($payload));
    }

    /**
     * @param string $line
     * @param array  $lineOptions
     *
     * @throws \Exception
     * @return mixed
     *
     */
    protected static function secureFromLine($line, array $lineOptions)
    {
        return SecureLine::fromLine($line, $lineOptions);
    }

    /**
     * @param array $line
     *
     * @return Message
     */
    protected static function messageFromLine(array $line)
    {
        return static::message($line['payload']);
    }

    /**
     * @param array $line
     *
     * @return Error
     */
    protected static function errorFromLine(array $line)
    {
        return static::error(LineDecoder::decode($line['payload']));
    }

    /**
     * @param array $line
     *
     * @return Rpc
     */
    protected static function rpcFromLine(array $line)
    {
        return static::rpc($line['target'], $line['payload'], $line['uniqid']);
    }

    /**
     * @param array $line
     *
     * @return Rpc
     */
    protected static function rpcErrorFromLine(array $line)
    {
        return static::rpcError($line['uniqid'], LineDecoder::decode($line['payload']));
    }

    /**
     * @param  array      $line
     * @return RpcSuccess
     */
    protected static function rpcSuccessFromLine(array $line)
    {
        return static::rpcSuccess($line['uniqid'], $line['payload']);
    }

    /**
     * @param  array      $line
     * @return RpcSuccess
     */
    protected static function rpcNotifyFromLine(array $line)
    {
        return static::rpcNotify($line['uniqid'], $line['payload']);
    }
}
