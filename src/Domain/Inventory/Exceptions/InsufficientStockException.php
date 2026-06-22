<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Exceptions;

use DomainException;

final class InsufficientStockException extends DomainException
{
    public function __construct(
        public readonly int $available,
        public readonly int $requested,
        public readonly ?string $sku = null,
    ) {
        parent::__construct(
            $sku === null
                ? sprintf('Insufficient stock: %d available, %d requested.', $available, $requested)
                : sprintf(
                    'Insufficient stock for product "%s": %d available, %d requested.',
                    $sku,
                    $available,
                    $requested,
                ),
        );
    }

    public static function forProduct(string $sku, int $available, int $requested): self
    {
        return new self(available: $available, requested: $requested, sku: $sku);
    }
}
