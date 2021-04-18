<?php
declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\CannotCloseTodo;
use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use App\Infrastructure\Repository\DoctrineDbalTodoRepository;
use App\Infrastructure\Repository\DoctrineOrmTodoRepository;
use App\Infrastructure\Repository\InMemoryEventStoreTodoRepository;
use App\Infrastructure\Repository\InMemoryTodoRepository;
use App\Infrastructure\Repository\Pdo\PdoTodoRepository;
use App\Infrastructure\Repository\PommFoundationTodoRepository;
use App\Infrastructure\Repository\ProophEventStoreTodoRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use PDO;
use PHPUnit\Framework\TestCase;
use PommProject\Foundation\Pomm;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\Pdo\Container\PostgresEventStoreFactory;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresAggregateStreamStrategy;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Psr\Container\ContainerInterface;

final class TodoRepositoryTest extends TestCase
{
    /**
     * @dataProvider provideConcretions
     */
    public function testTodoPersistence(TodoRepository $repository): void
    {
        $id = TodoId::generate();

        $todo = Todo::open($id, TodoDescription::fromString('Buy milk'));
        $repository->save($todo);

        $this->assertNull($repository->get(TodoId::generate()));

        $todo = $repository->get($id);
        $this->assertInstanceOf(Todo::class, $todo);
        $todo->close();
        $repository->save($todo);

        try {
            $todo = $repository->get($id);
            $todo->close();
        } catch (CannotCloseTodo) {
            return;
        }

        throw new \RuntimeException('Expecting to not be able to close already closed todo.');
    }

    public function provideConcretions(): \Generator
    {
        yield InMemoryTodoRepository::class => [new InMemoryTodoRepository()];

        yield PdoTodoRepository::class => [new PdoTodoRepository(new PDO($GLOBALS['PDO_DSN']))];

        yield DoctrineDbalTodoRepository::class => [new DoctrineDbalTodoRepository(DriverManager::getConnection(['url' => $GLOBALS['DOCTRINE_DBAL_URL']]))];

        yield DoctrineOrmTodoRepository::class => [new DoctrineOrmTodoRepository(EntityManager::create(
            ['url' => $GLOBALS['DOCTRINE_DBAL_URL']],
            Setup::createXMLMetadataConfiguration([dirname(dirname(dirname(__DIR__))).'/config/doctrine'], true)
        ))];

        yield PommFoundationTodoRepository::class => [new PommFoundationTodoRepository((new Pomm([
            'default' => ['dsn' => $GLOBALS['DOCTRINE_DBAL_URL']]
        ]))->getSession('default'))];

        yield InMemoryEventStoreTodoRepository::class => [new InMemoryEventStoreTodoRepository()];

        yield ProophEventStoreTodoRepository::class => [new ProophEventStoreTodoRepository(new PostgresEventStore(
            new FQCNMessageFactory(),
            new PDO($GLOBALS['PDO_DSN']),
            new PostgresSingleStreamStrategy()
        ))];
    }
}
