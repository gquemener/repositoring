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
use Prooph\Common\Messaging\DomainEvent;
use Prooph\Common\Messaging\Message;

final class InMemoryEventStoreTodoRepository implements TodoRepository, TodosRepository
{
    /** @var array<string, DomainEvent[]> */
    private $streams = [];

    public function get(TodoId $id): ?Todo
    {
        if (!isset($this->streams[$id->asString()])) {
            return null;
        }

        return Todo::replayHistory($this->streams[$id->asString()]);
    }

    public function save(Todo $todo): void
    {
        if (!isset($this->streams[$todo->id()->asString()])) {
            $this->streams[$todo->id()->asString()] = [];
        }

        $newStream = array_merge($this->streams[$todo->id()->asString()], $todo->releaseEvents());
        $versions = array_map(
            fn (Message $event): int => $event->metadata()['_aggregate_version'],
            $newStream
        );
        if (count($versions) !== count(array_unique($versions))) {
            throw CannotSaveTodo::becauseEntityHasChangedSinceLastRetrieval($todo->id());
        }

        $this->streams[$todo->id()->asString()] = $newStream;
    }

    public function opened(): iterable
    {
        $todos = [];
        foreach ($this->streams as $history) {
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
