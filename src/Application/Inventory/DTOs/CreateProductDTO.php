<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\DTOs;

/**
 * Typed input for creating a product. `sku` is optional: when omitted the use
 * case generates one. Prices are expressed in decimal reais.
 */
final readonly class CreateProductDTO
{
    private function __construct(
        public int $workspaceId,
        public string $name,
        public float $costPrice,
        public ?string $sku,
        public int $initialStock,
        public int $minimumStock,
        public ?int $categoryId,
        public ?string $description,
        public float $salePrice,
        public ?string $barcode,
        public string $unit,
    ) {}

    public static function from(
        int $workspaceId,
        string $name,
        float $costPrice,
        ?string $sku = null,
        int $initialStock = 0,
        int $minimumStock = 0,
        ?int $categoryId = null,
        ?string $description = null,
        float $salePrice = 0.0,
        ?string $barcode = null,
        string $unit = 'un',
    ): self {
        return new self(
            $workspaceId,
            $name,
            $costPrice,
            $sku,
            $initialStock,
            $minimumStock,
            $categoryId,
            $description,
            $salePrice,
            $barcode,
            $unit,
        );
    }
}
