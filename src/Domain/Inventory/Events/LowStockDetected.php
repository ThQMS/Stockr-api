<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Events;

use DateTimeImmutable;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;
use Stockr\Domain\Shared\DomainEvent;

/**
 * Raised when a product's on-hand stock reaches or drops below the given
 * threshold (its minimum/reorder level).
 */
final readonly class LowStockDetected implements DomainEvent
{
    public function __construct(
        public Product $product,
        public StockQuantity $threshold,
        private DateTimeImmutable $occurredOn = new DateTimeImmutable,
    ) {}

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
