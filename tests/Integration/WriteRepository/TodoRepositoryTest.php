<?php
declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use PHPUnit\Framework\TestCase;
use App\Domain\TodoRepository;
use App\Infrastructure\WriteRepository\InMemoryTodoRepository;
use App\Domain\TodoId;
use App\Domain\Todo;
use App\Domain\TodoDescription;

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
    }
}
