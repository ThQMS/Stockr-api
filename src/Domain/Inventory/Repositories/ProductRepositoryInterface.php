<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Repositories;

use Stockr\Domain\Inventory\Collections\ProductCollection;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;

/**
 * Persistence boundary for the Product aggregate. Implemented in Infrastructure.
 */
interface ProductRepositoryInterface
{
    /**
     * Look up by identity alone; workspace authorization is enforced by the
     * application layer via {@see Product::getWorkspaceId()}.
     */
    public function findById(string $id): ?Product;

    public function findBySku(ProductSku $sku, int $workspaceId): ?Product;

    public function existsBySku(ProductSku $sku, int $workspaceId): bool;

    public function countForWorkspace(int $workspaceId): int;

    public function save(Product $product): void;

    public function delete(Product $product): void;

    /**
     * Eager-load the whole workspace catalogue as a single collection (no N+1).
     */
    public function allForWorkspace(int $workspaceId): ProductCollection;

    public function lowStock(int $workspaceId): ProductCollection;
}
