<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Events;

use DateTimeImmutable;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Shared\DomainEvent;

/**
 * Raised when a new product enters the catalogue.
 */
final readonly class ProductCreated implements DomainEvent
{
    public function __construct(
        public Product $product,
        private DateTimeImmutable $occurredOn = new DateTimeImmutable,
    ) {}

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
