<?php
declare(strict_types=1);

namespace App\Domain;

final class TodoWasClosed
{
    public function __construct(
        public TodoId $id
    ) {
    }
}
