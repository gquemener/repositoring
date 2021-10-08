<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\Prooph\ProophEventStoreTodoRepository;
use PDO;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;

final class ProophEventStoreTest extends TodoRepositoryTest
{
    protected function getRepository(): TodoRepository
    {
        return new ProophEventStoreTodoRepository(new PostgresEventStore(
            new FQCNMessageFactory(),
            new PDO($GLOBALS['PDO_DSN']),
            new PostgresSingleStreamStrategy()
        ));
    }
}
