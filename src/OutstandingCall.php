<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger;

use Closure;
use React\Promise\Deferred;

use function is_callable;

final class OutstandingCall implements OutstandingCallInterface
{
    protected string $uniqid;

    protected Deferred $deferred;

    /** @var callable */
    protected $cleanup;

    /** @phpstan-ignore-next-line */
    public function __construct(string $uniqid, ?Closure $canceller = null, ?callable $cleanup = null) /** @phpstan-ignore-line */
    {
        if ($canceller !== null) {
            $canceller = Closure::bind($canceller, $this, self::class);
        }

        $this->uniqid   = $uniqid;
        $this->deferred = new Deferred($canceller);

        if (! is_callable($cleanup)) {
            $cleanup = static function (): void {
            };
        }

        $this->cleanup = $cleanup;
    }

    /**
     * @return mixed
     */
    public function getUniqid()
    {
        return $this->uniqid;
    }

    public function getDeferred(): Deferred
    {
        return $this->deferred;
    }

    /**
     * @param mixed $value
     */
    public function resolve($value): void
    {
        $cleanup = $this->cleanup;
        $cleanup($this);

        $this->deferred->resolve($value);
    }

    /**
     * @param mixed $value
     */
    public function reject($value): void
    {
        $cleanup = $this->cleanup;
        $cleanup($this);

        $this->deferred->reject($value);
    }
}
