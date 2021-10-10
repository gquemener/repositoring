<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\TodoId;
use Throwable;

final class CannotSaveTodo extends \RuntimeException
{
    public static function becauseEntityHasChangedSinceLastRetrieval(TodoId $id, ?Throwable $previous = null): self
    {
        return new self(sprintf('Todo "%s" has changed since its last retrieval', $id->asString()), 0, $previous);
    }
}
