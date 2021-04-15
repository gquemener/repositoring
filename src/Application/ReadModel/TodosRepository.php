<?php
declare(strict_types=1);

namespace App\Application\ReadModel;

interface TodosRepository
{
    public function opened(): array;
}
