<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Stockr\Domain\Inventory\Entities\Movement;
use Stockr\Domain\Inventory\Entities\Product;

/**
 * @property-read Product $resource
 *
 * @mixin Product
 */
final class ProductResource extends JsonResource
{
    /** @var array<string, mixed>|null */
    private ?array $category = null;

    /** @var list<Movement>|null */
    private ?array $movements = null;

    /**
     * Attach the product's category to be rendered (conditional include).
     *
     * @param  array<string, mixed>  $category
     */
    public function withCategory(array $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Attach recent movements to be rendered (conditional include).
     *
     * @param  list<Movement>  $movements
     */
    public function withMovements(array $movements): self
    {
        $this->movements = $movements;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        return [
            'id' => $product->getId(),
            'workspace_id' => $product->getWorkspaceId(),
            'category_id' => $product->getCategoryId(),
            'sku' => (string) $product->getSku(),
            'barcode' => $product->getBarcode(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'unit' => $product->getUnit(),
            'status' => $product->getStatus()->value,
            // Money is rendered as a formatted BRL string plus a numeric value;
            // StockQuantity is unwrapped to a plain int. is_low_stock inline.
            'cost_price' => $product->getCostPrice()->toCents() / 100,
            'cost_price_formatted' => $product->getCostPrice()->toReais(),
            'sale_price' => $product->getSalePrice()->toCents() / 100,
            'sale_price_formatted' => $product->getSalePrice()->toReais(),
            'current_stock' => $product->getCurrentStock()->getValue(),
            'minimum_stock' => $product->getMinimumStock()->getValue(),
            'is_low_stock' => $product->isBelowReorderLevel(),
            'qr_code_path' => $product->getQrCodePath(),

            // Related data is included only when explicitly attached by the
            // caller (domain entities carry no Eloquent relations, so there is
            // no whenLoaded() to lean on).
            'category' => $this->when($this->category !== null, fn () => $this->category),
            'movements' => $this->when(
                $this->movements !== null,
                fn () => MovementResource::collection($this->movements ?? []),
            ),
        ];
    }
}
