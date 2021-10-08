<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Domain\TodoRepository;
use App\Application\ReadModel\TodosRepository;
use App\Infrastructure\Repository\InMemoryEventStoreTodoRepository;

final class InMemoryEventStoreTest extends TodosRepositoryTest
{
    private InMemoryEventStoreTodoRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryEventStoreTodoRepository();
    }

    protected function getWriteModelRepository(): TodoRepository
    {
        return $this->repository;
    }

    protected function getReadModelRepository(): TodosRepository
    {
        return $this->repository;
    }
}
