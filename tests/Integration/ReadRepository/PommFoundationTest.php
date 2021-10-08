<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use App\Domain\TodoRepository;
use App\Application\ReadModel\TodosRepository;
use App\Infrastructure\Repository\PommFoundationTodoRepository;
use PommProject\Foundation\Pomm;
use PommProject\Foundation\Session\Session;

final class PommFoundationTest extends TodosRepositoryTest
{
    private Session $session;

    protected function setUp(): void
    {
        $this->session = (new Pomm([
            'default' => ['dsn' => $GLOBALS['DOCTRINE_DBAL_URL']]
        ]))->getSession('default');

        $this->session->getQueryManager()->query('TRUNCATE TABLE "'.PommFoundationTodoRepository::TABLE.'"');
    }

    protected function getWriteModelRepository(): TodoRepository
    {
        return new PommFoundationTodoRepository($this->session);
    }

    protected function getReadModelRepository(): TodosRepository
    {
        return new PommFoundationTodoRepository($this->session);
    }
}
