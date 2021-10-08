<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use App\Domain\TodoWasClosed;
use App\Domain\TodoWasOpened;
use App\Infrastructure\Repository\Prooph\OpenedTodoReadModel;
use App\Infrastructure\Repository\Prooph\ProophEventStoreTodoRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\EventStore\Pdo\Projection\PostgresProjectionManager;

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
        $this->assertCount(3, $readModelRepository->opened());
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
