<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use Closure;

use function array_key_exists;
use function array_values;
use function bin2hex;
use function microtime;
use function random_bytes;

final class OutstandingCalls
{
    /** @var array<OutstandingCall> */
    protected array $calls = [];

    public function newCall(Closure $canceller): OutstandingCall
    {
        $uniqid = $this->getNewUniqid();

        $this->calls[$uniqid] = new OutstandingCall($uniqid, $canceller, function (OutstandingCall $call): void {
            unset($this->calls[$call->getUniqid()]);
        });

        return $this->calls[$uniqid];
    }

    public function getCall(string $uniqid): OutstandingCall
    {
        return $this->calls[$uniqid];
    }

    /**
     * @return array<OutstandingCall>
     */
    public function getCalls(): array
    {
        return array_values($this->calls);
    }

    private function getNewUniqid(): string
    {
        do {
            $uniqid = (string) microtime(true) . '.' . bin2hex(random_bytes(4));
        } while (array_key_exists($uniqid, $this->calls));

        return $uniqid;
    }
}
