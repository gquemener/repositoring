<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\InMemoryEventStoreTodoRepository;

final class InMemoryEventStoreTest extends TodoRepositoryTest
{
    protected function getRepository(): TodoRepository
    {
        return new InMemoryEventStoreTodoRepository();
    }
}
