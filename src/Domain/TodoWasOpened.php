<?php
declare(strict_types=1);

namespace App\Domain;

use Prooph\Common\Messaging\DomainEvent;

final class TodoWasOpened extends DomainEvent
{
    public function __construct(
        public TodoId $id,
        public TodoDescription $description
    ) {
        $this->init();
    }

    protected function setPayload(array $payload): void
    {
        $this->id = TodoId::fromString($payload['id']);
        $this->description = TodoDescription::fromString($payload['description']);
    }

    public function payload(): array
    {
        return [
            'id' => $this->id->asString(),
            'description' => $this->description->asString(),
        ];
    }
}
