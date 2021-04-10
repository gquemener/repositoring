<?php
declare(strict_types=1);

namespace App\Domain;

final class CannotCloseTodo extends \LogicException
{
    public static function becauseTodoIsAlreadyClosed(TodoId $id): self
    {
        return new self(sprintf('Todo "%s" is already closed', $id->asString()));
    }
}
