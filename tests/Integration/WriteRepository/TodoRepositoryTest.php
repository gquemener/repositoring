<?php
declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use App\Infrastructure\WriteRepository\InMemoryTodoRepository;
use App\Infrastructure\WriteRepository\PdoTodoRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class TodoRepositoryTest extends TestCase
{
    /**
     * @dataProvider provideConcretions
     */
    public function testSaveGet(TodoRepository $repository): void
    {
        $id = TodoId::generate();
        $todo = Todo::open($id, TodoDescription::fromString('Buy milk'));

        $repository->save($todo);

        $this->assertInstanceOf(Todo::class, $repository->get($id));
    }

    public function provideConcretions(): \Generator
    {
        yield [new InMemoryTodoRepository()];

        yield [new PdoTodoRepository(new PDO($GLOBALS['DB_DSN']))];
    }
}
