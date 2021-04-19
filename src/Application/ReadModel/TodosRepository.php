<?php
declare(strict_types=1);

namespace App\Application\ReadModel;

interface TodosRepository
{
    /**
     * @return OpenedTodo[]
     */
    public function opened(): array;
}
