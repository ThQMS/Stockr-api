<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\UseCases;

use Stockr\Application\Inventory\DTOs\InventoryReportDTO;
use Stockr\Application\Inventory\DTOs\InventoryReportLineDTO;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Inventory\Services\StockCalculator;

/**
 * Builds an aggregated inventory snapshot for a workspace. The repository loads
 * the whole catalogue as a single ProductCollection (no N+1) and the pure
 * StockCalculator performs the value aggregation.
 */
final readonly class GetInventoryReportUseCase
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private StockCalculator $calculator,
    ) {}

    public function execute(int $workspaceId): InventoryReportDTO
    {
        $collection = $this->products->allForWorkspace($workspaceId);

        $lines = [];
        $totalUnits = 0;
        $lowStockCount = 0;

        foreach ($collection->toArray() as $product) {
            $lines[] = InventoryReportLineDTO::fromProduct($product);
            $totalUnits += $product->getCurrentStock()->getValue();
            $lowStockCount += $product->isBelowReorderLevel() ? 1 : 0;
        }

        return InventoryReportDTO::from(
            workspaceId: $workspaceId,
            totalProducts: $collection->count(),
            totalUnits: $totalUnits,
            totalStockValue: $this->calculator->calculateTotalValue($collection)->toCents() / 100,
            lowStockCount: $lowStockCount,
            lines: $lines,
        );
    }
}
