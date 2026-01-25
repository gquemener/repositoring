<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Pdo;

final class CouldNotExecuteQuery extends \RuntimeException
{
    /**
     * @param array<mixed> $errorInfo
     */
    public static function fromErrorInfo(array $errorInfo): self
    {
        $code = $errorInfo[0] ?? 'NO ERROR CODE AVAILABLE';
        $message = $errorInfo[2] ?? 'NO ERROR MESSAGE AVAILABLE';
        return new self(sprintf('[%s] %s', self::toString($code), self::toString($message)));
    }

    /**
     * @param mixed $value
     */
    private static function toString($value): string
    {
        if (!is_string($value)) {
            return '';
        }

        return $value;
    }
}
