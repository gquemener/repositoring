<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

use App\Domain\TodoRepository;
use App\Domain\Todo;
use App\Domain\TodoId;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\StreamName;
use Prooph\EventStore\Stream;

final class ProophEventStoreTodoRepository implements TodoRepository
{
    public function __construct(
        private EventStore $store
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        $name = $this->getStreamName($id);
        if (!$this->store->hasStream($name)) {
            return null;
        }

        return Todo::replayHistory(iterator_to_array($this->store->load($name)));
    }

    public function save(Todo $todo): void
    {
        $name = $this->getStreamName($todo->id());
        if (!$this->store->hasStream($name)) {
            $this->store->create(new Stream($name, new \EmptyIterator()));
        }

        $this->store->appendTo($name, new \ArrayIterator($todo->releaseEvents()));
    }

    private function getStreamName(TodoId $id): StreamName
    {
        return new StreamName(sprintf('todo-%s', $id->asString()));
    }
}
