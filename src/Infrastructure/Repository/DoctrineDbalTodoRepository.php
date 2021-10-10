<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use Doctrine\DBAL\Connection;
use App\Infrastructure\Repository\CannotSaveTodo;

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

        /** @var array{'id': string, 'description': string, 'status': string, 'version': int} $data */
        return Todo::fromData($data);
    }

    public function save(Todo $todo): void
    {
        $this->dbal->beginTransaction();
        $data = $todo->toData();
        $this->checkConcurrencyLock($todo->id(), $data['version']);
        $sql = <<<SQL
        INSERT INTO "doctrine_dbal_todo" ("id", "description", "status", "version") VALUES (:id, :description, :status, :version)
        ON CONFLICT ON CONSTRAINT "doctrine_dbal_todo_id" DO UPDATE SET status = :status, version = :version
        SQL;
        try {
            $this->dbal->executeStatement($sql, $todo->toData());
            $this->dbal->commit();
        } catch (\Exception $e) {
            $this->dbal->rollBack();

            throw $e;
        }
    }

    private function checkConcurrencyLock(TodoId $id, int $version): void
    {
        $res = $this->dbal->executeQuery(
            'SELECT COUNT(*) FROM "doctrine_dbal_todo" WHERE id = :id AND version >= :version LIMIT 1',
            [
                'id' => $id->asString(),
                'version' => $version
            ]
        );

        /** @var array{'count': int} */
        $data = $res->fetchAssociative();
        if (0 !== $data['count']) {
            throw CannotSaveTodo::becauseEntityHasChangedSinceLastRetrieval($id);
        }
    }
}
