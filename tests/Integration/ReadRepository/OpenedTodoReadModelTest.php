<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Domain\TodoRepository;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use App\Infrastructure\Repository\Prooph\ProophEventStoreTodoRepository;
use PDO;
use Prooph\EventStore\Pdo\Projection\PostgresProjectionManager;
use Prooph\EventStore\EventStore;
use App\Infrastructure\Repository\Prooph\OpenedTodoReadModel;
use App\Domain\TodoWasOpened;
use App\Domain\TodoWasClosed;

final class OpenedTodoReadModelTest extends TodosRepositoryTestCase
{
    private PDO $pdo;
    private EventStore $eventStore;

    protected function setUp(): void
    {
        $this->pdo = new PDO($GLOBALS['PDO_DSN']);
        $this->eventStore = new PostgresEventStore(new FQCNMessageFactory(), $this->pdo, new PostgresSingleStreamStrategy());
    }

    protected function getWriteModelRepository(): TodoRepository
    {
        $this->pdo->exec(<<<SQL
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
        SQL);

        return new ProophEventStoreTodoRepository($this->eventStore);
    }

    protected function getReadModelRepository(): TodosRepository
    {
        $this->pdo->exec('DROP TABLE IF EXISTS "'.OpenedTodoReadModel::TABLE_NAME.'"');
        $this->pdo->exec('TRUNCATE TABLE "projections"');
        $openedTodoReadModel = new OpenedTodoReadModel($this->pdo);
        $projectionManager = new PostgresProjectionManager($this->eventStore, $this->pdo);
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

        return $openedTodoReadModel;
    }
}
