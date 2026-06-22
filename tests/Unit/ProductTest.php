<?php

declare(strict_types=1);

use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\Exceptions\InsufficientStockException;
use Stockr\Domain\Inventory\ValueObjects\Money;
use Stockr\Domain\Inventory\ValueObjects\MovementType;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;

function makeProduct(int $stock = 10, int $reorder = 5): Product
{
    return new Product(
        id: 'prod-1',
        workspaceId: 1,
        sku: ProductSku::fromString('ABC-123'),
        name: 'Widget',
        price: Money::of(990),
        stock: StockQuantity::of($stock),
        reorderLevel: StockQuantity::of($reorder),
    );
}

it('adds stock on an inbound movement', function (): void {
    $product = makeProduct(stock: 10);

    $product->applyMovement(MovementType::In, StockQuantity::of(5));

    expect($product->stock()->getValue())->toBe(15);
});

it('removes stock on an outbound movement', function (): void {
    $product = makeProduct(stock: 10);

    $product->applyMovement(MovementType::Out, StockQuantity::of(4));

    expect($product->stock()->getValue())->toBe(6);
});

it('rejects an outbound movement that would go negative', function (): void {
    $product = makeProduct(stock: 3);

    $product->applyMovement(MovementType::Out, StockQuantity::of(10));
})->throws(InsufficientStockException::class);

it('flags low stock at or below the reorder level', function (): void {
    $product = makeProduct(stock: 6, reorder: 5);
    expect($product->isBelowReorderLevel())->toBeFalse();

    $product->applyMovement(MovementType::Out, StockQuantity::of(1));
    expect($product->isBelowReorderLevel())->toBeTrue();
});
