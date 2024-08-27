<?php

declare(strict_types=1);

namespace App\Tests\Integration\ReadRepository;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Repository\DoctrineOrmTodoRepository;
use App\Domain\TodoRepository;
use App\Application\ReadModel\TodosRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Setup;

final class DoctrineOrmTest extends TodosRepositoryTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $config = ORMSetup::createXMLMetadataConfiguration(
            paths: [dirname(dirname(dirname(__DIR__))).'/config/doctrine'],
            isDevMode: true,
            isXsdValidationEnabled: false,
        );

        $connection  = DriverManager::getConnection(
            (new DsnParser())->parse($GLOBALS['DOCTRINE_DSN']),
            $config
        );
        $this->em = new EntityManager($connection, $config);
        $connection->executeStatement('TRUNCATE TABLE "doctrine_orm_todo"');
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
