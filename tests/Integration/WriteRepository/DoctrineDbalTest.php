<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\TodoRepository;
use App\Infrastructure\Repository\DoctrineDbalTodoRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\ORMSetup;

final class DoctrineDbalTest extends TodoRepositoryTestCase
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

        return new DoctrineDbalTodoRepository($connection);
    }
}
