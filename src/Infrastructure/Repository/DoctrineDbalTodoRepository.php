<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use Doctrine\DBAL\Connection;

final class DoctrineDbalTodoRepository implements TodoRepository
{
    public function __construct(
        private Connection $dbal
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        $res = $this->dbal->executeQuery(
            'SELECT * from "doctrine_dbal_todo" WHERE id = :id',
            ['id' => $id->asString()]
        );
        if (false === $data = $res->fetchAssociative()) {
            return null;
        }

        /** @var array{'id': string, 'description': string, 'status': string} $data */
        return Todo::fromData($data);
    }

    public function save(Todo $todo): void
    {
        $this->dbal->beginTransaction();
        $sql = <<<SQL
        INSERT INTO "doctrine_dbal_todo" ("id", "description", "status") VALUES (:id, :description, :status)
        ON CONFLICT ON CONSTRAINT "doctrine_dbal_todo_id" DO UPDATE SET status = :status
        SQL;
        try {
            $this->dbal->executeStatement($sql, $todo->toData());
            $this->dbal->commit();
        } catch (\Exception $e) {
            $this->dbal->rollBack();

            throw $e;
        }
    }
}
