<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\DTOs;

use Stockr\Domain\Inventory\Entities\Movement;
use Stockr\Domain\Inventory\Entities\Product;

/**
 * Result of scanning a product: its identity and current figures, a derived
 * stock status, and a snapshot of its most recent movements.
 */
final readonly class ScanResultDTO
{
    /**
     * @param  list<array{id: int|null, type: string, quantity: int, notes: string|null, movedAt: string}>  $recentMovements
     */
    private function __construct(
        public string $productId,
        public string $sku,
        public string $name,
        public int $stock,
        public float $price,
        public string $stockStatus,
        public array $recentMovements,
    ) {}

    /**
     * @param  list<Movement>  $recentMovements
     */
    public static function from(Product $product, array $recentMovements): self
    {
        $stock = $product->getCurrentStock()->getValue();

        $status = match (true) {
            $stock === 0 => 'out_of_stock',
            $product->isBelowReorderLevel() => 'low',
            default => 'ok',
        };

        $movements = array_map(
            static fn (Movement $m): array => [
                'id' => $m->id,
                'type' => $m->type->value,
                'quantity' => $m->quantity->getValue(),
                'notes' => $m->notes,
                'movedAt' => $m->movedAt->format(DATE_ATOM),
            ],
            $recentMovements,
        );

        return new self(
            productId: (string) $product->getId(),
            sku: (string) $product->sku(),
            name: $product->name(),
            stock: $stock,
            price: $product->getCostPrice()->toCents() / 100,
            stockStatus: $status,
            recentMovements: $movements,
        );
    }
}
