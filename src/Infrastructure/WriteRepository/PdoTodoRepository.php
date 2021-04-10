<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

use App\Domain\TodoRepository;
use App\Domain\Todo;
use App\Domain\TodoId;
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
        if (false === $res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }

        return Todo::fromData($res);
    }

    public function save(Todo $todo): void
    {
        $this->pdo->beginTransaction();
        $sql = <<<SQL
        INSERT INTO "pdo_todo" ("id", "description", "status") VALUES (:id, :description, :status)
        ON CONFLICT ON CONSTRAINT "pdo_todo_id" DO UPDATE SET status = :status
        SQL;
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute($todo->toData());
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollback();

            throw $e;
        }
    }
}
