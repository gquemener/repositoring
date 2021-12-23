<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Prooph;

use Prooph\EventStore\Projection\AbstractReadModel;
use PDO;
use PDOStatement;
use App\Application\ReadModel\TodosRepository;
use App\Application\ReadModel\OpenedTodo;
use App\Infrastructure\Repository\Pdo\CouldNotExecuteQuery;

final class OpenedTodoReadModel extends AbstractReadModel implements TodosRepository
{
    public const TABLE_NAME = 'prooph_read_opened_todo';

    public function __construct(
        private PDO $connection
    ) {
    }

    public function init(): void
    {
        $sql = <<<SQL
            CREATE TABLE "%s" (
                "id" uuid NOT NULL,
                "description" text NOT NULL,
                CONSTRAINT "%s_id" PRIMARY KEY ("id")
            )
        SQL;
        $this->connection->exec(sprintf($sql, self::TABLE_NAME, self::TABLE_NAME));
    }

    public function isInitialized(): bool
    {
        $stmt = $this->connection->query(sprintf(
            'SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename = \'%s\'',
            self::TABLE_NAME
        ));

        if (!$stmt instanceof PDOStatement) {
            return false;
        }

        /** @var array<int> */
        $res = $stmt->fetch();

        return $res[0] > 0;
    }

    public function reset(): void
    {
        $this->connection->exec(sprintf('DROP TABLE "%s"', self::TABLE_NAME));
    }

    public function delete(): void
    {
        $this->connection->exec(sprintf('TRUNCATE TABLE "%s"', self::TABLE_NAME));
    }

    /**
     * OPERATIONS
     */

    /**
     * @param array{'id': string, 'description': string} $data
     */
    public function insert(array $data): void
    {
        $stmt = $this->connection->prepare(sprintf(
            'INSERT INTO "%s" (id, description) VALUES (:id, :description)',
            self::TABLE_NAME
        ));

        $stmt->execute($data);
    }

    /**
     * @param array{'id': string} $data
     */
    public function remove(array $data): void
    {
        $stmt = $this->connection->prepare(sprintf(
            'DELETE FROM "%s" WHERE id = :id',
            self::TABLE_NAME
        ));

        $stmt->execute($data);
    }

    public function opened(): iterable
    {
        $stmt = $this->connection->query(sprintf('SELECT * FROM "%s"', self::TABLE_NAME), PDO::FETCH_ASSOC);

        if (!$stmt instanceof PDOStatement) {
            throw CouldNotExecuteQuery::fromErrorInfo($this->connection->errorInfo());
        }

        $resultSet = $stmt->fetchAll();
        if (false === $resultSet) {
            throw CouldNotExecuteQuery::fromErrorInfo($this->connection->errorInfo());
        }

        foreach ($resultSet as $data) {
            $todo = new OpenedTodo();
            $todo->id = $data['id'];
            $todo->description = $data['description'];
            yield $todo;
        }
    }
}
