<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\UseCases;

use Stockr\Application\Inventory\DTOs\CreateProductDTO;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\Exceptions\DuplicateSkuException;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Inventory\ValueObjects\Money;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;
use Stockr\Domain\Shared\EventDispatcherInterface;

/**
 * Creates a product: validates SKU uniqueness within the workspace (generating
 * one when none is supplied), persists it and publishes the events the
 * aggregate recorded (ProductCreated).
 */
final readonly class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private EventDispatcherInterface $events,
    ) {}

    public function execute(CreateProductDTO $dto): Product
    {
        $sku = $dto->sku !== null && trim($dto->sku) !== ''
            ? $this->ensureUnique(ProductSku::fromString($dto->sku), $dto->workspaceId)
            : $this->generateSku($dto);

        $product = Product::create(
            workspaceId: $dto->workspaceId,
            sku: $sku,
            name: $dto->name,
            price: Money::of((int) round($dto->costPrice * 100)),
            stock: StockQuantity::of($dto->initialStock),
            reorderLevel: StockQuantity::of($dto->minimumStock),
            categoryId: $dto->categoryId,
            description: $dto->description,
            salePrice: Money::of((int) round($dto->salePrice * 100)),
            barcode: $dto->barcode,
            unit: $dto->unit,
        );

        $this->products->save($product);

        foreach ($product->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }

        return $product;
    }

    private function ensureUnique(ProductSku $sku, int $workspaceId): ProductSku
    {
        if ($this->products->existsBySku($sku, $workspaceId)) {
            throw DuplicateSkuException::inWorkspace((string) $sku, $workspaceId);
        }

        return $sku;
    }

    private function generateSku(CreateProductDTO $dto): ProductSku
    {
        $letters = preg_replace('/[^A-Za-z]/', '', $dto->name) ?? '';
        $code = strtoupper(substr($letters, 0, 6));
        $code = strlen($code) >= 2 ? $code : 'PRD';

        $sequence = $this->products->countForWorkspace($dto->workspaceId) + 1;

        do {
            $sku = ProductSku::generate($code, $sequence);
            $sequence++;
        } while ($this->products->existsBySku($sku, $dto->workspaceId));

        return $sku;
    }
}
