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

    /**
     * @param array{'id': string, 'description': string} $payload
     */
    protected function setPayload(array $payload): void
    {
        $this->id = TodoId::fromString($payload['id']);
        $this->description = TodoDescription::fromString($payload['description']);
    }

    /**
     * @return array{'id': string, 'description': string}
     */
    public function payload(): array
    {
        return [
            'id' => $this->id->asString(),
            'description' => $this->description->asString(),
        ];
    }
}
