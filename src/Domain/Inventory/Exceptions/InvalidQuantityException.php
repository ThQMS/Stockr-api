<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Exceptions;

use DomainException;

final class InvalidQuantityException extends DomainException
{
    public static function negative(int $value): self
    {
        return new self(sprintf('Quantity cannot be negative, got %d.', $value));
    }

    public static function notPositive(int $value): self
    {
        return new self(sprintf('Quantity must be a positive integer, got %d.', $value));
    }
}
