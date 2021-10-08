<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\PommFoundationTodoRepository;
use PommProject\Foundation\Pomm;

final class PommFoundationTest extends TodoRepositoryTest
{
    protected function getRepository(): TodoRepository
    {
        return new PommFoundationTodoRepository(
            (new Pomm(['default' => ['dsn' => $GLOBALS['DOCTRINE_DBAL_URL']]]))
                ->getSession('default')
        );
    }
}
