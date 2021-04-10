<?php
declare(strict_types=1);

namespace App\Domain;

final class TodoStatus
{
    private const VALUES = [
        'open'
    ];

    private function __construct(
        private string $value
    ) {
    }

    public static function open(): self
    {
        return self::fromString('open');
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::VALUES, true)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a valid status',
                $value
            ));
        }

        return new self($value);
    }


    public function asString(): string
    {
        return $this->value;
    }
}
