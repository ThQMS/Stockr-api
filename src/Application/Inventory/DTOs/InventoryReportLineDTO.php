<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\DTOs;

use Stockr\Domain\Inventory\Entities\Product;

/**
 * A single product line inside an InventoryReportDTO.
 */
final readonly class InventoryReportLineDTO
{
    private function __construct(
        public string $productId,
        public string $sku,
        public string $name,
        public int $stock,
        public int $reorderLevel,
        public float $unitPrice,
        public float $lineValue,
        public bool $isLowStock,
    ) {}

    public static function fromProduct(Product $product): self
    {
        $stock = $product->getCurrentStock()->getValue();
        $lineValueCents = $product->getCostPrice()->multiply($stock)->toCents();

        return new self(
            productId: (string) $product->getId(),
            sku: (string) $product->sku(),
            name: $product->name(),
            stock: $stock,
            reorderLevel: $product->getMinimumStock()->getValue(),
            unitPrice: $product->getCostPrice()->toCents() / 100,
            lineValue: $lineValueCents / 100,
            isLowStock: $product->isBelowReorderLevel(),
        );
    }
}
