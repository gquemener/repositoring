<?php

declare(strict_types=1);

namespace App\Domain;

final class Todo
{
    private function __construct(
        private TodoId $id,
        private TodoDescription $description,
        private TodoStatus $status
    ) {
    }

    public static function open(TodoId $id, TodoDescription $description): self
    {
        return new self($id, $description, TodoStatus::open());
    }

    public function id(): TodoId
    {
        return $this->id;
    }

    public static function fromData(array $data): self
    {
        return new self(
            TodoId::fromString($data['id']),
            TodoDescription::fromString($data['description']),
            TodoStatus::fromString($data['status'])
        );
    }

    public function toData(): array
    {
        return [
            'id' => $this->id->asString(),
            'description' => $this->description->asString(),
            'status' => $this->status->asString(),
        ];
    }
}
