<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Entities;

use DateTimeImmutable;
use Stockr\Domain\Inventory\ValueObjects\MovementType;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;

/**
 * A single, immutable stock movement registered against a product. Carries
 * before/after snapshots so the ledger is a full audit trail.
 */
final class Movement
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $workspaceId,
        public readonly string $productId,
        public readonly int $userId,
        public readonly MovementType $type,
        public readonly StockQuantity $quantity,
        public readonly int $quantityBefore,
        public readonly int $quantityAfter,
        public readonly ?string $notes,
        public readonly ?string $referenceCode,
        public readonly DateTimeImmutable $movedAt,
    ) {}

    /**
     * Return a copy of this movement with its persisted identity assigned.
     */
    public function withId(int $id): self
    {
        return new self(
            id: $id,
            workspaceId: $this->workspaceId,
            productId: $this->productId,
            userId: $this->userId,
            type: $this->type,
            quantity: $this->quantity,
            quantityBefore: $this->quantityBefore,
            quantityAfter: $this->quantityAfter,
            notes: $this->notes,
            referenceCode: $this->referenceCode,
            movedAt: $this->movedAt,
        );
    }

    /**
     * Signed effect this movement has on the product's on-hand stock.
     */
    public function signedQuantity(): int
    {
        return match ($this->type) {
            MovementType::In => $this->quantity->getValue(),
            MovementType::Out, MovementType::Transfer => -$this->quantity->getValue(),
            MovementType::Adjustment => $this->quantity->getValue(),
        };
    }
}
