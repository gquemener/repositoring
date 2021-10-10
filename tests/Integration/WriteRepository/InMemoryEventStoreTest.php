<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\InMemoryEventStoreTodoRepository;

final class InMemoryEventStoreTest extends TodoRepositoryTest
{
    private InMemoryEventStoreTodoRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryEventStoreTodoRepository();
    }

    protected function getRepository(): TodoRepository
    {
        return $this->repository;
    }
}
