<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

use App\Domain\TodoRepository;
use App\Domain\Todo;
use App\Domain\TodoId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineOrmTodoRepository implements TodoRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        return $this->entityManager->getRepository(Todo::class)->find($id->asString());
    }

    public function save(Todo $todo): void
    {
        $this->entityManager->persist($todo);
        $this->entityManager->flush();
    }
}
