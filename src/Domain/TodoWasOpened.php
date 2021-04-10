<?php
declare(strict_types=1);

namespace App\Domain;

final class TodoWasOpened
{
    public function __construct(
        public TodoId $id,
        public TodoDescription $description
    ) {
    }
}
