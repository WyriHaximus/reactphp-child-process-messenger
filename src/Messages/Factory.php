<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

class Factory
{
    /**
     * @param string $line
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function fromLine($line, array $lineOptions)
    {
        $line = json_decode($line, true);
        $method = $line['type'] . 'FromLine';
        if (method_exists(static::class, $method)) {
            return static::$method($line, $lineOptions);
        }

        throw new \Exception('Unknown message type: ' . $line['type']);
    }

    /**
     * @param string $line
     * @param array $lineOptions
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected static function secureFromLine($line, array $lineOptions)
    {
        return SecureLine::fromLine($line, $lineOptions);
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
     * @param array $line
     *
     * @return Message
     */
    protected static function messageFromLine(array $line)
    {
        return static::message($line['payload']);
    }

    /**
     * @param string $target
     * @param array $payload
     *
     * @return Rpc
     */
    public static function rpc($target, array $payload = [], $uniqid = '')
    {
        return new Rpc($target, new Payload($payload), $uniqid);
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
     * @param string $target
     * @param array $payload
     *
     * @return Rpc
     */
    public static function rpc_error($uniqid, array $payload = [])
    {
        return new RpcError($uniqid, new Payload($payload));
    }

    /**
     * @param array $line
     *
     * @return Rpc
     */
    protected static function rpc_errorFromLine(array $line)
    {
        return static::rpc_error($line['uniqid'], $line['payload']);
    }

    /**
     * @param string $uniqid
     * @param array $payload
     *
     * @return RpcSuccess
     */
    public static function rpc_success($uniqid, array $payload = [])
    {
        return new RpcSuccess($uniqid, new Payload($payload));
    }

    /**
     * @param array $line
     * @return RpcSuccess
     */
    protected static function rpc_successFromLine(array $line)
    {
        return static::rpc_success($line['uniqid'], $line['payload']);
    }
}
