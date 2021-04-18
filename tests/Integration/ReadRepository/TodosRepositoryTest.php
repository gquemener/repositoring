<?php
declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use PHPUnit\Framework\TestCase;
use PDO;
use App\Infrastructure\Repository\Pdo\PdoTodoRepository;
use App\Infrastructure\Repository\Pdo\PdoTodosRepository;
use App\Infrastructure\Repository\InMemoryTodoRepository;
use App\Infrastructure\Repository\InMemoryEventStoreTodoRepository;

final class TodosRepositoryTest extends TestCase
{
    /**
     * @dataProvider provideConcretions
     */
    public function testListOpenedTodos(
        TodoRepository $writeModelRepository,
        TodosRepository $readModelRepository,
        ?callable $setuper = null
    ): void {
        if ($setuper) {
            $setuper();
        }

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

        $inMemoryEventStoreTodoRepository = new InMemoryEventStoreTodoRepository();
        yield InMemoryEventStoreTodoRepository::class => [$inMemoryEventStoreTodoRepository, $inMemoryEventStoreTodoRepository];

        $pdo = new PDO($GLOBALS['PDO_DSN']);
        yield PdoTodosRepository::class => [
            new PdoTodoRepository($pdo),
            new PdoTodosRepository($pdo),
            (fn (PDO $conn): callable => fn (): int => $conn->exec('TRUNCATE TABLE "pdo_todo"'))($pdo),
        ];
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
