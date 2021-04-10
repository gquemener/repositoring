<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;

final class InMemoryTodoRepository implements TodoRepository
{
    private array $storage = [];

    public function get(TodoId $id): ?Todo
    {
        if (!isset($this->storage[$id->asString()])) {
            return null;
        }

        return Todo::fromData(
            $this->storage[$id->asString()]
        );
    }

    public function save(Todo $todo): void
    {
        $this->storage[$todo->id()->asString()] = $todo->toData();
    }
}
