<?php

declare(strict_types=1);

namespace WyriHaximus\React\ChildProcess\Messenger\Messages;

use Exception;

use function base64_encode;
use function hash_equals;
use function hash_hmac;
use function Safe\base64_decode;
use function Safe\json_encode;

final class SecureLine implements LineInterface
{
    protected ActionableMessageInterface $line;

    protected string $key;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(ActionableMessageInterface $line, array $options)
    {
        $this->line = $line;
        $this->key  = $options['key'];
    }

    public function __toString(): string
    {
        $line = json_encode($this->line);

        return json_encode([
            'type' => 'secure',
            'line' => $line,
            'signature' => base64_encode(static::sign($line, $this->key)),
        ]) . LineInterface::EOL;
    }

    /**
     * @param array<mixed> $line
     * @param array<mixed> $lineOptions
     *
     * @throws Exception
     */
    public static function fromLine(array $line, array $lineOptions): ActionableMessageInterface
    {
        if (static::validate(base64_decode($line['signature'], true), $line['line'], $lineOptions['key'])) {
            return Factory::fromLine($line['line'], $lineOptions);
        }

        /** @phpstan-ignore-next-line  */
        throw new Exception('Signature mismatch!');
    }

    private static function sign(string $line, string $key): string
    {
        return hash_hmac('sha256', $line, $key, true);
    }

    private static function validate(string $signature, string $line, string $key): bool
    {
        return hash_equals($signature, static::sign($line, $key));
    }
}
