<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Application\ReadModel\OpenedTodo;
use App\Application\ReadModel\TodosRepository;
use App\Domain\Todo;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use App\Domain\TodoStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectRepository;

final class DoctrineOrmTodoRepository implements TodoRepository, TodosRepository
{
    /** @var EntityRepository<Todo> */
    private EntityRepository $entityRepository;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $entityRepository = $this->entityManager->getRepository(Todo::class);
        if (!$entityRepository instanceof EntityRepository) {
            throw new \InvalidArgumentException();
        }
        $this->entityRepository = $entityRepository;
    }

    public function get(TodoId $id): ?Todo
    {
        return $this->entityRepository->find($id->asString());
    }

    public function save(Todo $todo): void
    {
        try {
            $this->entityManager->persist($todo);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            throw CannotSaveTodo::becauseEntityHasChangedSinceLastRetrieval($todo->id(), $e);
        }
    }

    public function opened(): array
    {
        $result = $this
            ->entityRepository
            ->createQueryBuilder('todo')
            ->where('todo.status.value = :status')
            ->setParameter('status', TodoStatus::opened()->asString())
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map(function(array $data): OpenedTodo {
            $todo = new OpenedTodo();
            $todo->id = $data['id'];
            $todo->description = $data['description.value'];

            return $todo;
        }, $result);
    }
}
