<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Events;

use DateTimeImmutable;
use Stockr\Domain\Inventory\Entities\Movement;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Shared\DomainEvent;

/**
 * Raised when a stock movement has been applied to a product and persisted.
 * Carries both the movement and the resulting product state.
 */
final readonly class StockMovementRegistered implements DomainEvent
{
    public function __construct(
        public Movement $movement,
        public Product $product,
        private DateTimeImmutable $occurredOn = new DateTimeImmutable,
    ) {}

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
