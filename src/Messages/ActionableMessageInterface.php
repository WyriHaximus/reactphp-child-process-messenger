<?php

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

interface ActionableMessageInterface
{
    /**
     * @param $bindTo
     * @param $source
     * @return void
     */
    public function handle($bindTo, $source);
}
