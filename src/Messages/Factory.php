<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

class Factory
{
    /**
     * @param $line
     * @return mixed
     * @throws \Exception
     */
    public static function fromLine($line)
    {
        $line = json_decode($line, true);
        $method = $line['type'] . 'FromLine';
        if (method_exists(self::class, $method)) {
            return self::$method($line);
        }

        throw new \Exception();
    }

    /**
     * @param array $payload
     * @return Message
     */
    public static function message(array $payload = [])
    {
        return new Message(new Payload($payload));
    }

    /**
     * @param array $line
     * @return Message
     */
    protected static function messageFromLine(array $line)
    {
        return self::message($line['payload']);
    }
}
