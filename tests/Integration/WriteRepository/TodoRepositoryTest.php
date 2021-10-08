<?php

declare(strict_types=1);

namespace App\Tests\Integration\WriteRepository;

use App\Domain\CannotCloseTodo;
use App\Domain\Todo;
use App\Domain\TodoDescription;
use App\Domain\TodoId;
use App\Domain\TodoRepository;
use PHPUnit\Framework\TestCase;

abstract class TodoRepositoryTest extends TestCase
{
    public function testTodoPersistence(): void
    {
        $repository = $this->getRepository();
        $id = TodoId::generate();

        $todo = Todo::open($id, TodoDescription::fromString('Buy milk'));
        $repository->save($todo);

        $this->assertNull($repository->get(TodoId::generate()));

        $todo = $repository->get($id);
        $this->assertInstanceOf(Todo::class, $todo);
        $todo->close();
        $repository->save($todo);

        try {
            $todo = $repository->get($id);
            $todo->close();
        } catch (CannotCloseTodo) {
            return;
        }

        throw new \RuntimeException('Expecting to not be able to close already closed todo.');
    }

    abstract protected function getRepository(): TodoRepository;
}
