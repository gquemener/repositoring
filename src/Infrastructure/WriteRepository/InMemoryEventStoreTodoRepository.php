<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;

final class InMemoryEventStoreTodoRepository implements TodoRepository
{
    private $streams = [];

    public function get(TodoId $id): ?Todo
    {
        if (!isset($this->streams[$id->asString()])) {
            return null;
        }

        $todo = Todo::replayHistory($this->streams[$id->asString()]);

        return $todo;
    }

    public function save(Todo $todo): void
    {
        if (!isset($this->streams[$todo->id()->asString()])) {
            $this->streams[$todo->id()->asString()] = [];
        }

        $this->streams[$todo->id()->asString()] = array_merge($this->streams[$todo->id()->asString()], $todo->releaseEvents());
    }
}
