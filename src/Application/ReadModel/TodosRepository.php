<?php
declare(strict_types=1);

namespace App\Application\ReadModel;

interface TodosRepository
{
    /**
     * @return iterable<OpenedTodo>
     */
    public function opened(): iterable;
}
