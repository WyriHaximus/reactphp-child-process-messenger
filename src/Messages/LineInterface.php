<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

interface LineInterface
{
    //const EOL = PHP_EOL;
    const EOL = "\n";

    /**
     * @param ActionableMessageInterface $line
     * @param array $options
     */
    public function __construct(\JsonSerializable $line, array $options);

    /**
     * @return string
     */
    public function __toString();
}
