<?php
declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use App\Infrastructure\WriteRepository\InMemoryTodoRepository;
use PHPUnit\Framework\TestCase;

final class TodosRepositoryTest extends TestCase
{
    /**
     * @dataProvider provideConcretions
     */
    public function testListOpenedTodos(
        TodoRepository $writeModelRepository,
        TodosRepository $readModelRepository
    ): void {
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->closedTodo());
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->closedTodo());
        $writeModelRepository->save($this->closedTodo());

        $this->assertCount(3, $readModelRepository->opened());
    }

    public function provideConcretions(): \Generator
    {
        $inMemoryRepository = new InMemoryTodoRepository();
        yield InMemoryTodoRepository::class => [$inMemoryRepository, $inMemoryRepository];
    }

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
