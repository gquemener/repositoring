<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use App\Application\ReadModel\TodosRepository;
use App\Application\ReadModel\OpenedTodo;
use App\Infrastructure\Repository\CannotSaveTodo;

final class InMemoryTodoRepository implements TodoRepository, TodosRepository
{
    /** @var array<string, array{'id': string, 'description': string, 'status': string, 'version': int}> */
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
        $data = $todo->toData();
        $key = $todo->id()->asString();
        if (isset($this->storage[$key])) {
            if ($this->storage[$key]['version'] >= $data['version']) {
                throw CannotSaveTodo::becauseEntityHasChangedSinceLastRetrieval($todo->id());
            }
        }
        $this->storage[$todo->id()->asString()] = $data;
    }

    public function opened(): iterable
    {
        foreach ($this->storage as $data) {
            if ('opened' === $data['status']) {
                $todo = new OpenedTodo();
                $todo->id = $data['id'];
                $todo->description = $data['description'];
                yield $todo;
            }
        }
    }
}
