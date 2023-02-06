<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Infrastructure\Repository\InMemoryTodoRepository;
use App\Domain\TodoRepository;

final class InMemoryTest extends TodosRepositoryTestCase
{
    private InMemoryTodoRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTodoRepository();
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
