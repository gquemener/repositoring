<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Domain\TodoRepository;
use App\Infrastructure\Repository\Prooph\ProophEventStoreTodoRepository;
use PDO;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;

final class ProophEventStoreTest extends TodosRepositoryTest
{
    private EventStore $eventStore;

    protected function setUp(): void
    {
        $pdo = new PDO($GLOBALS['PDO_DSN']);
        $this->eventStore = new PostgresEventStore(new FQCNMessageFactory(), $pdo, new PostgresSingleStreamStrategy());

        $pdo->exec(<<<SQL
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
    }

    protected function getWriteModelRepository(): TodoRepository
    {
        return new ProophEventStoreTodoRepository($this->eventStore);
    }

    protected function getReadModelRepository(): TodosRepository
    {
        return new ProophEventStoreTodoRepository($this->eventStore);
    }
}
