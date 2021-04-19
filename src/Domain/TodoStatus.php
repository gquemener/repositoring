<?php
declare(strict_types=1);

namespace App\Domain;

/**
 * @method static TodoStatus opened()
 * @method static TodoStatus closed()
 */
final class TodoStatus
{
    private const VALUES = [
        'opened',
        'closed'
    ];

    private function __construct(
        private string $value
    ) {
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): self
    {
        return self::fromString($name);
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

    public function equals(object $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $other->value === $this->value;
    }
}
