<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Prooph;

use App\Domain\TodoRepository;
use App\Domain\Todo;
use App\Domain\TodoId;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\StreamName;
use Prooph\EventStore\Stream;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Metadata\Operator;
use App\Application\ReadModel\TodosRepository;
use App\Domain\TodoWasOpened;
use App\Domain\TodoWasClosed;
use App\Application\ReadModel\OpenedTodo;
use Prooph\EventStore\Exception\ConcurrencyException;
use App\Infrastructure\Repository\CannotSaveTodo;

final class ProophEventStoreTodoRepository implements TodoRepository, TodosRepository
{
    private const STREAM_NAME = 'todo';

    public function __construct(
        private EventStore $store
    ) {
    }

    public function get(TodoId $id): ?Todo
    {
        $name = $this->getStreamName();
        if (!$this->store->hasStream($name)) {
            return null;
        }

        $matcher = (new MetadataMatcher())
            ->withMetadataMatch('_aggregate_id', Operator::EQUALS(), $id->asString())
            ->withMetadataMatch('_aggregate_type', Operator::EQUALS(), Todo::class)
        ;

        try {
            return Todo::replayHistory($this->store->load($name, 1, null, $matcher));
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function save(Todo $todo): void
    {
        $name = $this->getStreamName();
        if (!$this->store->hasStream($name)) {
            $this->store->create(new Stream($name, new \EmptyIterator()));
        }

        $events = array_map(
            fn (DomainEvent $event) => $event
                ->withAddedMetadata('_aggregate_id', $todo->id()->asString())
                ->withAddedMetadata('_aggregate_type', Todo::class),
            $todo->releaseEvents()
        );
        try {
            $this->store->appendTo($name, new \ArrayIterator($events));
        } catch (ConcurrencyException $e) {
            throw CannotSaveTodo::becauseEntityHasChangedSinceLastRetrieval($todo->id(), $e);
        }
    }

    private function getStreamName(): StreamName
    {
        return new StreamName(self::STREAM_NAME);
    }

    public function opened(): iterable
    {
        $name = $this->getStreamName();

        $matcher = (new MetadataMatcher())
            ->withMetadataMatch('_aggregate_type', Operator::EQUALS(), Todo::class)
        ;

        $todos = [];

        /** @var DomainEvent $event */
        foreach ($this->store->load($name, 1, null, $matcher) as $event) {
            switch ($event::class) {
                case TodoWasOpened::class:
                    $todo = new OpenedTodo();
                    $todo->id = $event->id->asString();
                    $todo->description = $event->description->asString();
                    $todos[$todo->id] = $todo;
                    break;

                case TodoWasClosed::class:
                    unset($todos[$event->id->asString()]);
                    break;
            }
        }

        return $todos;
    }
}
