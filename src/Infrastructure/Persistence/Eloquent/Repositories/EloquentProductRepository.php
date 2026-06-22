<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Repositories;

use Stockr\Domain\Inventory\Collections\ProductCollection;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;
use Stockr\Infrastructure\Persistence\Eloquent\Mappers\ProductMapper;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;

/**
 * Eloquent-backed implementation of the Product persistence boundary. Every read
 * returns a domain Product (via ProductMapper) — never an Eloquent model.
 */
final readonly class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private ProductMapper $mapper,
    ) {}

    public function findById(string $id): ?Product
    {
        $model = ProductModel::query()->find($id);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function findBySku(ProductSku $sku, int $workspaceId): ?Product
    {
        $model = ProductModel::query()
            ->where('sku', (string) $sku)
            ->where('workspace_id', $workspaceId)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function existsBySku(ProductSku $sku, int $workspaceId): bool
    {
        return ProductModel::query()
            ->where('sku', (string) $sku)
            ->where('workspace_id', $workspaceId)
            ->exists();
    }

    public function countForWorkspace(int $workspaceId): int
    {
        return ProductModel::query()->where('workspace_id', $workspaceId)->count();
    }

    public function save(Product $product): void
    {
        ProductModel::updateOrCreate(
            ['id' => $product->getId()],
            [
                'workspace_id' => $product->getWorkspaceId(),
                'category_id' => $product->getCategoryId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'sku' => $product->getSku()->getValue(),
                'barcode' => $product->getBarcode(),
                'current_stock' => $product->getCurrentStock()->getValue(),
                'minimum_stock' => $product->getMinimumStock()->getValue(),
                'cost_price_cents' => $product->getCostPrice()->toCents(),
                'sale_price_cents' => $product->getSalePrice()->toCents(),
                'unit' => $product->getUnit(),
                'status' => $product->getStatus()->value,
                'qr_code_path' => $product->getQrCodePath(),
            ],
        );
    }

    public function delete(Product $product): void
    {
        if ($product->getId() !== null) {
            ProductModel::query()->where('id', $product->getId())->delete();
        }
    }

    public function allForWorkspace(int $workspaceId): ProductCollection
    {
        $products = ProductModel::query()
            ->where('workspace_id', $workspaceId)
            ->orderBy('name')
            ->get()
            ->map(fn (ProductModel $m): Product => $this->mapper->toDomain($m))
            ->all();

        return ProductCollection::fromArray($products);
    }

    public function lowStock(int $workspaceId): ProductCollection
    {
        $products = ProductModel::query()
            ->where('workspace_id', $workspaceId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->get()
            ->map(fn (ProductModel $m): Product => $this->mapper->toDomain($m))
            ->all();

        return ProductCollection::fromArray($products);
    }
}
