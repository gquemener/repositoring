<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\DoctrineOrmTodoRepository;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Setup;

final class DoctrineOrmTest extends TodoRepositoryTestCase
{
    protected function getRepository(): TodoRepository
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

        return new DoctrineOrmTodoRepository(new EntityManager($connection, $config));
    }
}
