<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Todo;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use Cycle\ORM\ORM;
use Cycle\ORM\Transaction;

final class CycleOrmTodoRepository implements TodoRepository
{
    public function __construct(
        private ORM $orm
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        return null;
    }

    public function save(Todo $todo): void
    {
        $tr = new Transaction($this->orm);
        $tr->persist($todo);
        $tr->run();
    }
}
