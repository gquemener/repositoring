<?php
declare(strict_types=1);

namespace App\Infrastructure\WriteRepository;

use App\Domain\TodoRepository;
use App\Domain\Todo;
use App\Domain\TodoId;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\StreamName;
use Prooph\EventStore\Stream;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Metadata\Operator;

final class ProophEventStoreTodoRepository implements TodoRepository
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

        return Todo::replayHistory($this->store->load($name, 1, null, $matcher));
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
                ->withAddedMetadata('_aggregate_type', Todo::class)
            ,
            $todo->releaseEvents()
        );
        $this->store->appendTo($name, new \ArrayIterator($events));
    }

    private function getStreamName(): StreamName
    {
        return new StreamName(self::STREAM_NAME);
    }
}
