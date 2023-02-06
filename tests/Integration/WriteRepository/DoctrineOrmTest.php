<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\DoctrineOrmTodoRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

final class DoctrineOrmTest extends TodoRepositoryTestCase
{
    protected function getRepository(): TodoRepository
    {
        return new DoctrineOrmTodoRepository(EntityManager::create(
            ['url' => $GLOBALS['DOCTRINE_DBAL_URL']],
            Setup::createXMLMetadataConfiguration([dirname(dirname(dirname(__DIR__))).'/config/doctrine'], true)
        ));
    }
}
