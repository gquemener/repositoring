<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\DoctrineDbalTodoRepository;
use Doctrine\DBAL\DriverManager;

final class DoctrineDbalTest extends TodoRepositoryTestCase
{
    protected function getRepository(): TodoRepository
    {
        return new DoctrineDbalTodoRepository(
            DriverManager::getConnection(['url' => $GLOBALS['DOCTRINE_DBAL_URL']])
        );
    }
}
