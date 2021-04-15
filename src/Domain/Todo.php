<?php

declare(strict_types=1);

namespace App\Domain;

use Prooph\Common\Messaging\DomainEvent;

final class Todo
{
    private string $id; // Doctrine ORM prevents using embeddable TodoId as entity identifier

    private int $no; // Doctrine ORM version

    private int $version = 1; // Prooph ES version

    private TodoDescription $description;

    private TodoStatus $status;

    private array $events = [];

    private function __construct()
    {
    }

    public static function open(TodoId $id, TodoDescription $description): self
    {
        $self = new self($id, $description, TodoStatus::opened());
        $self->record(new TodoWasOpened($id, $description));

        return $self;
    }

    public function id(): TodoId
    {
        return TodoId::fromString($this->id);
    }

    public function close(): void
    {
        if ($this->status->equals(TodoStatus::closed())) {
            throw CannotCloseTodo::becauseTodoIsAlreadyClosed($this->id());
        }

        $this->record(new TodoWasClosed());
    }

    public static function fromData(array $data): self
    {
        $self = new self();
        $self->id = TodoId::fromString($data['id'])->asString();
        $self->description = TodoDescription::fromString($data['description']);
        $self->status = TodoStatus::fromString($data['status']);

        return $self;
    }

    public function toData(): array
    {
        return [
            'id' => $this->id()->asString(),
            'description' => $this->description->asString(),
            'status' => $this->status->asString(),
        ];
    }

    public static function replayHistory(iterable $events): self
    {
        if (!is_array($events) && !$events instanceof \Countable) {
            throw new \InvalidArgumentException('Could not count events');
        }

        if (0 === count($events)) {
            throw new \InvalidArgumentException('Cannot replay an empty history');
        }

        $self = new self();
        foreach ($events as $event) {
            $self->apply($event);
        }

        return $self;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    private function onTodoWasOpened(TodoWasOpened $event): void
    {
        $this->id = $event->id->asString();
        $this->description = $event->description;
        $this->status = TodoStatus::opened();
    }

    private function onTodoWasClosed(TodoWasClosed $event): void
    {
        $this->status = TodoStatus::closed();
    }

    private function apply(object $event): void
    {
        $name = substr(get_class($event), strrpos(get_class($event), '\\') + 1);
        $this->{'on'.$name}($event);
        $this->version++;
    }

    private function record(DomainEvent $event): void
    {
        $this->apply($event);

        $event = $event->withAddedMetadata('_aggregate_version', $this->version - 1);

        $this->events[] = $event;
    }
}
