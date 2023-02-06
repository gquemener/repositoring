<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use PHPUnit\Framework\TestCase;

abstract class TodosRepositoryTest extends TestCase
{
    public function testListOpenedTodos(): void
    {
        $writeModelRepository = $this->getWriteModelRepository();
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->closedTodo());
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->closedTodo());
        $writeModelRepository->save($this->closedTodo());

        $readModelRepository = $this->getReadModelRepository();
        $this->assertCount(3, iterator_to_array($readModelRepository->opened()));
    }

    abstract protected function getWriteModelRepository(): TodoRepository;

    abstract protected function getReadModelRepository(): TodosRepository;

    private function openedTodo(): Todo
    {
        return Todo::open(TodoId::generate(), TodoDescription::fromString('Buy milk'));
    }

    private function closedTodo(): Todo
    {
        $todo = $this->openedTodo();
        $todo->close();

        return $todo;
    }
}
