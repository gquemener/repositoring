<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use App\Application\ReadModel\TodosRepository;
use App\Application\ReadModel\OpenedTodo;

final class InMemoryTodoRepository implements TodoRepository, TodosRepository
{
    /** @var array<string, array{'id': string, 'description': string, 'status': string}> */
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

    public function opened(): array
    {
        $todos = [];
        foreach ($this->storage as $data) {
            if ('opened' === $data['status']) {
                $todo = new OpenedTodo();
                $todo->id = $data['id'];
                $todo->description = $data['description'];
                $todos[] = $todo;
            }
        }

        return $todos;
    }
}
