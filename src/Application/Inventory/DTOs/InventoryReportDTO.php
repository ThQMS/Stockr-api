<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\DTOs;

/**
 * Aggregated inventory snapshot for a workspace.
 */
final readonly class InventoryReportDTO
{
    /**
     * @param  list<InventoryReportLineDTO>  $lines
     */
    private function __construct(
        public int $workspaceId,
        public int $totalProducts,
        public int $totalUnits,
        public float $totalStockValue,
        public int $lowStockCount,
        public array $lines,
    ) {}

    /**
     * @param  list<InventoryReportLineDTO>  $lines
     */
    public static function from(
        int $workspaceId,
        int $totalProducts,
        int $totalUnits,
        float $totalStockValue,
        int $lowStockCount,
        array $lines,
    ): self {
        return new self(
            $workspaceId,
            $totalProducts,
            $totalUnits,
            $totalStockValue,
            $lowStockCount,
            $lines,
        );
    }
}
