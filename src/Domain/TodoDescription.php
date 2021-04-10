<?php
declare(strict_types=1);

namespace App\Domain;

final class TodoDescription
{
    private function __construct(
        private string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Description must not be empty');
        }

        return new self($value);
    }

    public function asString(): string
    {
        return $this->value;
    }
}
