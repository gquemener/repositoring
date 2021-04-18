<?php
declare(strict_types=1);

namespace App\Domain;

use Prooph\Common\Messaging\DomainEvent;

final class TodoWasClosed extends DomainEvent
{
    public function __construct(
        public TodoId $id
    ) {
        $this->init();
    }

    protected function setPayload(array $payload): void
    {
        $this->id = TodoId::fromString($payload['id']);
    }

    public function payload(): array
    {
        return [
            'id' => $this->id->asString(),
        ];
    }
}
