<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

use App\Domain\TodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use PommProject\Foundation\Session\Session;

final class PommFoundationTodoRepository implements TodoRepository
{
    public function __construct(
        private Session $session
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        $qm = $this->session->getQueryManager();

        $query = $qm->query('SELECT * FROM "pomm_foundation_todo" WHERE id = $*', [$id->asString()]);
        if ($query->isEmpty()) {
            return null;
        }

        return Todo::fromData($query->current());
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
