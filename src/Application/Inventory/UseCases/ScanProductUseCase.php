<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\UseCases;

use Stockr\Application\Inventory\DTOs\ScanResultDTO;
use Stockr\Domain\Inventory\Exceptions\ProductNotFoundException;
use Stockr\Domain\Inventory\Repositories\MovementRepositoryInterface;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;

/**
 * Resolves a product from a scanned code (a "stockr://product/{workspace}/{sku}"
 * QR payload or a bare SKU) and returns it together with a stock status and its
 * five most recent movements.
 */
final readonly class ScanProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private MovementRepositoryInterface $movements,
    ) {}

    public function execute(int $workspaceId, string $scannedCode): ScanResultDTO
    {
        $sku = ProductSku::fromString($this->extractSku($scannedCode));

        $product = $this->products->findBySku($sku, $workspaceId)
            ?? throw ProductNotFoundException::forSku((string) $sku);

        $recent = $this->movements->recentForProduct((string) $product->getId(), 5);

        return ScanResultDTO::from($product, $recent);
    }

    private function extractSku(string $code): string
    {
        if (str_starts_with($code, 'stockr://product/')) {
            $segments = explode('/', $code);

            return (string) end($segments);
        }

        return trim($code);
    }
}
