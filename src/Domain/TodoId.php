<?php
declare(strict_types=1);

namespace App\Domain;

use Symfony\Component\Uid\Uuid;

final class TodoId
{
    public function __construct(
        private string $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v4()->__toString());
    }

    public static function fromString(string $id): self
    {
        if (!Uuid::isValid($id)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid UUID', $id));
        }

        return new self($id);
    }

    public function asString(): string
    {
        return $this->value;
    }
}
