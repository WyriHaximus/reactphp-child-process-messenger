<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

/**
 * @todo code smell with $source
 */
trait OnDataTrait
{
    /**
     * @param string $data
     * @param string $source
     */
    protected function onData($data, $source)
    {
        $this->buffers[$source] .= $data;

        if (strpos($this->buffers[$source], PHP_EOL) !== false) {
            $messages = explode(PHP_EOL, $this->buffers[$source]);
            $this->buffers[$source] = array_pop($messages);
            $this->iterateMessages($messages, $source);
        }
    }

    /**
     * @param array $messages
     * @param string $source
     */
    protected function iterateMessages(array $messages, $source)
    {
        foreach ($messages as $message) {
            $this->handleMessage(json_decode($message, true), $source);
        }
    }
}
