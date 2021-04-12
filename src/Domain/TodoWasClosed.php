<?php
declare(strict_types=1);

namespace App\Domain;

use Prooph\Common\Messaging\DomainEvent;

final class TodoWasClosed extends DomainEvent
{
    public function __construct()
    {
        $this->init();
    }

    protected function setPayload(array $payload): void
    {
    }

    public function payload(): array
    {
        return [];
    }
}
