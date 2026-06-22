<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Exceptions;

use DomainException;

final class InvalidSkuException extends DomainException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf('The SKU "%s" is invalid.', $value));
    }

    public static function empty(): self
    {
        return new self('A SKU cannot be empty.');
    }
}
