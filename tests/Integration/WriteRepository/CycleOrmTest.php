<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Infrastructure\Repository\CycleOrmTodoRepository;
use Cycle\ORM\ORM;
use App\Domain\TodoRepository;

final class CycleOrmTest extends TodoRepositoryTest
{
    private ORM $cycle;

    protected function setUp(): void
    {
        $cycleDbal = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases'   => [
                'default' => [
                    'connection' => 'postgres'
                ]
            ],
            'connections' => [
                'postgres' => [
                    'driver'  => \Spiral\Database\Driver\Postgres\PostgresDriver::class,
                    'connection' => $GLOBALS['PDO_DSN'],
                    'options' => [
                    'username' => 'bruce',
                    'password' => 'mypass',
                    ]
                ]
            ]
        ]));
        $schemaBuilder = require dirname(dirname(dirname(__DIR__))).'/config/cycle/schema.php';
        $this->cycle = new ORM(
            new Factory($cycleDbal),
            $schemaBuilder($cycleDbal)
        );
    }
    protected function getRepository(): TodoRepository
    {
        return new CycleOrmTodoRepository($this->cycle);
    }
}
