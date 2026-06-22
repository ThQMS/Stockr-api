<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Services;

use Stockr\Domain\Inventory\Collections\ProductCollection;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\ValueObjects\Money;

/**
 * Pure domain service: stateless inventory maths with zero framework dependency.
 */
final class StockCalculator
{
    /**
     * Total value held in stock across a collection of products. Works in
     * integer cents (Money) throughout to avoid floating-point imprecision.
     */
    public function calculateTotalValue(ProductCollection $products): Money
    {
        return array_reduce(
            $products->toArray(),
            fn (Money $carry, Product $product): Money => $carry->add(
                $product->getCostPrice()->multiply(
                    $product->getCurrentStock()->getValue(),
                ),
            ),
            Money::of(0),
        );
    }

    /**
     * Products at or below their minimum stock, ordered by criticality
     * (currentStock / minimumStock ascending — the most depleted come first).
     */
    public function getCriticalProducts(ProductCollection $products): ProductCollection
    {
        $lowStock = array_filter(
            $products->toArray(),
            fn (Product $p): bool => $p->getCurrentStock()->isBelow($p->getMinimumStock()),
        );

        usort($lowStock, function (Product $a, Product $b): int {
            $ratioA = $a->getCurrentStock()->getValue() / max(1, $a->getMinimumStock()->getValue());
            $ratioB = $b->getCurrentStock()->getValue() / max(1, $b->getMinimumStock()->getValue());

            return $ratioA <=> $ratioB;
        });

        return ProductCollection::fromArray($lowStock);
    }
}
