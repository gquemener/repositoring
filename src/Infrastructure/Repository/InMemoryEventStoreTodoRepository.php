<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use App\Application\ReadModel\TodosRepository;
use App\Application\ReadModel\OpenedTodo;
use App\Domain\TodoWasOpened;
use App\Domain\TodoWasClosed;

final class InMemoryEventStoreTodoRepository implements TodoRepository, TodosRepository
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

    public function opened(): array
    {
        $todos = [];
        foreach ($this->streams as $aggregateId => $history) {
            foreach ($history as $event) {
                switch (get_class($event)) {
                    case TodoWasOpened::class:
                        $todo = new OpenedTodo();
                        $todo->id = $event->id->asString();
                        $todo->description = $event->description->asString();
                        $todos[$event->id->asString()] = $todo;
                        break;

                    case TodoWasClosed::class:
                        unset($todos[$event->id->asString()]);
                        break;
                }
            }
        }

        return $todos;
    }
}