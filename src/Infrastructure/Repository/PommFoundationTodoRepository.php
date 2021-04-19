<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use PommProject\Foundation\Session;
use Countable;

final class PommFoundationTodoRepository implements TodoRepository
{
    public function __construct(
        private Session $session
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        $qm = $this->session->getQueryManager();

        $resultSet = $qm->query('SELECT * FROM "pomm_foundation_todo" WHERE id = $*', [$id->asString()]);
        if (!$resultSet instanceof Countable) {
            throw new \RuntimeException('Unable to count result set');
        }

        if (0 === count($resultSet)) {
            return null;
        }

        return Todo::fromData($resultSet->current());
    }

    public function save(Todo $todo): void
    {
        $qm = $this->session->getQueryManager();

        $sql = <<<SQL
        INSERT INTO "pomm_foundation_todo" ("id", "description", "status") VALUES ($*, $*, $*)
        ON CONFLICT ON CONSTRAINT "pomm_foundation_todo_id" DO UPDATE SET status = $*
        SQL;

        $data = $todo->toData();
        $qm->query('BEGIN');
        $qm->query($sql, [$data['id'], $data['description'], $data['status'], $data['status']]);
        $qm->query('COMMIT');
    }
}
