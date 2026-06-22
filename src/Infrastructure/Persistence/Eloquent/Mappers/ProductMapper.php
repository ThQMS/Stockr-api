<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Mappers;

use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\ValueObjects\Money;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;

/**
 * Single source of truth for translating a ProductModel row into the
 * framework-free Product aggregate.
 */
final class ProductMapper
{
    public function toDomain(ProductModel $model): Product
    {
        return new Product(
            id: $model->id,
            workspaceId: $model->workspace_id,
            sku: ProductSku::fromString($model->sku),
            name: $model->name,
            price: Money::of($model->cost_price_cents),
            stock: StockQuantity::of($model->current_stock),
            reorderLevel: StockQuantity::of($model->minimum_stock),
            categoryId: $model->category_id,
            description: $model->description,
            status: $model->status,
            salePrice: Money::of($model->sale_price_cents),
            barcode: $model->barcode,
            unit: $model->unit,
            qrCodePath: $model->qr_code_path,
        );
    }
}
