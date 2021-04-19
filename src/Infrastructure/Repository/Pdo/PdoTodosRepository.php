<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository\Pdo;

use App\Application\ReadModel\TodosRepository;
use PDO;
use PDOStatement;
use App\Application\ReadModel\OpenedTodo;

final class PdoTodosRepository implements TodosRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function opened(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM "pdo_opened_todo"', PDO::FETCH_ASSOC);

        if (!$stmt instanceof PDOStatement) {
            throw CouldNotExecuteQuery::fromErrorInfo($this->pdo->errorInfo());
        }

        $resultSet = $stmt->fetchAll();
        if (false === $resultSet) {
            throw CouldNotExecuteQuery::fromErrorInfo($this->pdo->errorInfo());
        }

        return array_map(
            function(array $data): OpenedTodo {
                $todo = new OpenedTodo();
                $todo->id = $data['id'];
                $todo->description = $data['description'];

                return $todo;
            },
            $resultSet
        );
    }
}
