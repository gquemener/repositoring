<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Application\ReadModel\TodosRepository;
use App\Domain\TodoRepository;
use App\Infrastructure\Repository\Pdo\PdoTodoRepository;
use App\Infrastructure\Repository\Pdo\PdoTodosRepository;
use PDO;

final class PdoTest extends TodosRepositoryTestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO($GLOBALS['PDO_DSN']);
        $this->pdo->exec('TRUNCATE TABLE "pdo_todo"');
    }

    protected function getWriteModelRepository(): TodoRepository
    {
        return new PdoTodoRepository($this->pdo);
    }

    protected function getReadModelRepository(): TodosRepository
    {
        return new PdoTodosRepository($this->pdo);
    }
}
