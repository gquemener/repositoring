<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

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

        return Todo::fromData($data);
    }

    public function save(Todo $todo): void
    {
        $this->dbal->beginTransaction();
        try{
            $this->dbal->insert('doctrine_dbal_todo', $todo->toData());
            $this->dbal->commit();
        } catch (\Exception $e) {
            $this->dbal->rollBack();

            throw $e;
        }
    }
}
