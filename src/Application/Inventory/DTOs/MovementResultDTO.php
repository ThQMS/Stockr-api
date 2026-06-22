<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\DTOs;

use Stockr\Domain\Inventory\Entities\Movement;
use Stockr\Domain\Inventory\Entities\Product;

/**
 * Outcome of registering a movement, returned by RegisterMovementUseCase.
 */
final readonly class MovementResultDTO
{
    private function __construct(
        public int $movementId,
        public string $productId,
        public string $type,
        public int $quantity,
        public int $resultingStock,
        public bool $lowStockTriggered,
    ) {}

    public static function fromMovementAndProduct(Movement $movement, Product $product): self
    {
        return new self(
            movementId: (int) $movement->id,
            productId: (string) $product->getId(),
            type: $movement->type->value,
            quantity: $movement->quantity->getValue(),
            resultingStock: $product->getCurrentStock()->getValue(),
            lowStockTriggered: $product->isBelowReorderLevel(),
        );
    }
}
