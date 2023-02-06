<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\InMemoryTodoRepository;

final class InMemoryTest extends TodoRepositoryTestCase
{
    private InMemoryTodoRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTodoRepository();
    }

    protected function getRepository(): TodoRepository
    {
        return $this->repository;
    }
}
