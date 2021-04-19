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
use App\Infrastructure\Repository\Prooph\ProophEventStoreTodoRepository;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use App\Infrastructure\Repository\Prooph\OpenedTodoReadModel;
use Prooph\EventStore\Pdo\Projection\PostgresProjectionManager;
use App\Domain\TodoWasOpened;
use App\Domain\TodoWasClosed;

final class TodosRepositoryTest extends TestCase
{
    /**
     * @dataProvider provideConcretions
     */
    public function testListOpenedTodos(
        TodoRepository $writeModelRepository,
        TodosRepository $readModelRepository,
        ?callable $setupWriteModel = null,
        ?callable $setupReadModel = null
    ): void {
        if ($setupWriteModel) {
            $setupWriteModel();
        }

        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->closedTodo());
        $writeModelRepository->save($this->openedTodo());
        $writeModelRepository->save($this->closedTodo());
        $writeModelRepository->save($this->closedTodo());

        if ($setupReadModel) {
            $setupReadModel();
        }
        $this->assertCount(3, $readModelRepository->opened());
    }

    public function provideConcretions(): \Generator
    {
        $inMemoryRepository = new InMemoryTodoRepository();
        yield InMemoryTodoRepository::class => [$inMemoryRepository, $inMemoryRepository];

        $inMemoryEventStoreTodoRepository = new InMemoryEventStoreTodoRepository();
        yield InMemoryEventStoreTodoRepository::class => [$inMemoryEventStoreTodoRepository, $inMemoryEventStoreTodoRepository];

        $executeSql = fn(PDO $conn): callable => fn($sql): callable => fn (): int => $conn->exec($sql);

        $pdo = new PDO($GLOBALS['PDO_DSN']);
        yield PdoTodosRepository::class => [
            new PdoTodoRepository($pdo),
            new PdoTodosRepository($pdo),
            $executeSql($pdo)('TRUNCATE TABLE "pdo_todo"')
        ];

        $pdo = new PDO($GLOBALS['PDO_DSN']);
        $openedTodoReadModel = new OpenedTodoReadModel($pdo);
        $eventStore = new PostgresEventStore(new FQCNMessageFactory(), $pdo, new PostgresSingleStreamStrategy());
        yield OpenedTodoReadModel::class => [
            new ProophEventStoreTodoRepository($eventStore),
            $openedTodoReadModel,
            $executeSql($pdo)(<<<SQL
                DO $$
                    DECLARE
                        name text;
                    BEGIN
                        FOR name IN SELECT stream_name FROM event_streams
                        LOOP
                            EXECUTE 'DROP TABLE ' || quote_ident(name);
                        END LOOP;
                        TRUNCATE TABLE "event_streams";
                    END;
                $$;
            SQL),
            function() use ($executeSql, $pdo, $eventStore, $openedTodoReadModel) {
                $executeSql($pdo)('TRUNCATE TABLE "prooph_read_opened_todo"');

                $projectionManager = new PostgresProjectionManager($eventStore, $pdo);
                $projectionManager
                    ->createReadModelProjection('opened_todo', $openedTodoReadModel)
                    ->fromStream('todo')
                    ->when([
                        TodoWasOpened::class => function ($state, TodoWasOpened $event) {
                            $this->readModel()->stack('insert', [
                                'id' => $event->id->asString(),
                                'description' => $event->description->asString(),
                            ]);
                        },
                        TodoWasClosed::class => function ($state, TodoWasClosed $event) {
                            $this->readModel()->stack('remove', [
                                'id' => $event->id->asString(),
                            ]);
                        },
                    ])
                    ->run(false)
                ;
            }
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
