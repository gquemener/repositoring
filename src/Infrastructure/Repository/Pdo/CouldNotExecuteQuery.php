<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository\Pdo;

final class CouldNotExecuteQuery extends \RuntimeException
{
    /**
     * @param array<int, string> $errorInfo
     */
    public static function fromErrorInfo(array $errorInfo): self
    {
        return new self(sprintf('[%s] %s', $errorInfo[0], $errorInfo[2]));
    }
}
