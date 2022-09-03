<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Pdo;

use App\Domain\Todo;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use App\Infrastructure\Repository\CannotSaveTodo;
use LogicException;
use PDO;

final class PdoTodoRepository implements TodoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        $stmt = $this->pdo->prepare('SELECT * from "pdo_todo" WHERE id = :id');
        $stmt->execute([':id' => $id->asString()]);

        /** @var array{id: string, description: string, status: string, version: int}|false */
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (false === $res) {
            return null;
        }

        return Todo::fromData($res);
    }

    public function save(Todo $todo): void
    {
        $this->pdo->beginTransaction();

        $data = $todo->toData();
        $this->checkConcurrencyLock($todo->id(), $data['version']);

        $sql = <<<SQL
        INSERT INTO "pdo_todo" ("id", "description", "status", "version") VALUES (:id, :description, :status, :version)
            ON CONFLICT ON CONSTRAINT "pdo_todo_id" DO
                UPDATE SET status = :status, version = :version
        SQL;
        $stmt = $this->pdo->prepare($sql);
        try {
            $res = $stmt->execute($data);
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();

            throw $e;
        }
    }

    private function checkConcurrencyLock(TodoId $id, int $version): void
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM "pdo_todo" WHERE id = :id AND version >= :version LIMIT 1');
        $stmt->execute([
            'id' => $id->asString(),
            'version' => $version
        ]);

        if (false === $res = $stmt->fetch(PDO::FETCH_NUM)) {
            throw new LogicException('Expected concurrency check query failed.');
        }

        /** @var array<int> $res */
        if (0 !== $res[0]) {
            throw CannotSaveTodo::becauseEntityHasChangedSinceLastRetrieval($id);
        }
    }
}
