<?php
declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use App\Infrastructure\WriteRepository\DoctrineDbalTodoRepository;
use App\Infrastructure\WriteRepository\InMemoryTodoRepository;
use App\Infrastructure\WriteRepository\PdoTodoRepository;
use Doctrine\DBAL\DriverManager;
use PDO;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\WriteRepository\InMemoryEventStoreTodoRepository;
use App\Domain\CannotCloseTodo;
use App\Infrastructure\WriteRepository\DoctrineOrmTodoRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use App\Infrastructure\WriteRepository\PommFoundationTodoRepository;
use PommProject\Foundation\Pomm;
use App\Infrastructure\WriteRepository\ProophEventStoreTodoRepository;
use Prooph\EventStore\Pdo\Container\PostgresEventStoreFactory;
use Psr\Container\ContainerInterface;
use Prooph\EventStore\Pdo\PostgresEventStore;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresAggregateStreamStrategy;
use Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy;

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
