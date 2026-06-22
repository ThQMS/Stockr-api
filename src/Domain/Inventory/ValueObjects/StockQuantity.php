<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\ValueObjects;

use Stockr\Domain\Inventory\Exceptions\InsufficientStockException;
use Stockr\Domain\Inventory\Exceptions\InvalidQuantityException;

/**
 * A non-negative amount of stock units. Immutable; every operation returns a
 * new instance and the constructor guarantees the value can never be negative.
 */
final class StockQuantity
{
    private function __construct(
        private readonly int $value,
    ) {
        if ($value < 0) {
            throw InvalidQuantityException::negative($value);
        }
    }

    public static function of(int $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        if ($this->value < $other->value) {
            throw new InsufficientStockException(
                available: $this->value,
                requested: $other->value,
            );
        }

        return new self($this->value - $other->value);
    }

    public function isBelow(self $minimum): bool
    {
        return $this->value < $minimum->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
