<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Repository\DoctrineOrmTodoRepository;
use App\Domain\TodoRepository;
use App\Application\ReadModel\TodosRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

final class DoctrineOrmTest extends TodosRepositoryTest
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = EntityManager::create(
            ['url' => $GLOBALS['DOCTRINE_DBAL_URL']],
            Setup::createXMLMetadataConfiguration([dirname(dirname(dirname(__DIR__))).'/config/doctrine'], true)
        );
        $this->em->getConnection()->exec('TRUNCATE TABLE "doctrine_orm_todo"');
    }

    protected function getWriteModelRepository(): TodoRepository
    {
        return new DoctrineOrmTodoRepository($this->em);
    }

    protected function getReadModelRepository(): TodosRepository
    {
        return new DoctrineOrmTodoRepository($this->em);
    }
}
