<?php

namespace WyriHaximus\React\ChildProcess\Messenger;

use WyriHaximus\React\ChildProcess\Messenger\Messages\Line;

/**
 * @todo code smell with $source
 */
trait OnDataTrait
{
    /**
     * @var string[]
     */
    protected $buffers = [
        'stdin' => '',
        'stdout' => '',
        'stderr' => '',
    ];
    /**
     * @param string $data
     * @param string $source
     */
    protected function onData($data, $source)
    {
        $this->buffers[$source] .= $data;

        if (strpos($this->buffers[$source], Line::EOL) !== false) {
            $messages = explode(Line::EOL, $this->buffers[$source]);
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

    /**
     * @param array $message
     * @param $source
     * @return void
     */
    abstract protected function handleMessage(array $message, $source);
}
