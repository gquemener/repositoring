<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\Pdo\PdoTodoRepository;
use PDO;

final class PdoTest extends TodoRepositoryTest
{
    protected function getRepository(): TodoRepository
    {
        return new PdoTodoRepository(new PDO($GLOBALS['PDO_DSN']));
    }
}
