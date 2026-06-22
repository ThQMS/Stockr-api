<?php

declare(strict_types=1);

use Stockr\Domain\Inventory\Collections\ProductCollection;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\Events\LowStockDetected;
use Stockr\Domain\Inventory\Events\ProductCreated;
use Stockr\Domain\Inventory\Services\StockCalculator;
use Stockr\Domain\Inventory\ValueObjects\Money;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;
use Stockr\Domain\Shared\DomainEvent;

function product(string $sku, int $stock, int $minimum, int $priceCents = 1000): Product
{
    return new Product(
        id: null,
        workspaceId: 1,
        sku: ProductSku::fromString($sku),
        name: $sku,
        price: Money::of($priceCents),
        stock: StockQuantity::of($stock),
        reorderLevel: StockQuantity::of($minimum),
    );
}

it('sums total stock value in cents', function (): void {
    $products = ProductCollection::fromArray([
        product('AAA-001', stock: 3, minimum: 1, priceCents: 1000), // 30,00
        product('BBB-002', stock: 2, minimum: 1, priceCents: 250),  //  5,00
    ]);

    $total = (new StockCalculator)->calculateTotalValue($products);

    expect($total->toCents())->toBe(3500)
        ->and($total->toReais())->toBe('R$ 35,00');
});

it('returns an empty value for an empty collection', function (): void {
    $total = (new StockCalculator)->calculateTotalValue(ProductCollection::empty());

    expect($total->toCents())->toBe(0);
});

it('orders critical products by ascending criticality ratio', function (): void {
    $products = ProductCollection::fromArray([
        product('OKAY-001', stock: 50, minimum: 10), // not critical
        product('MILD-002', stock: 8, minimum: 10),  // ratio 0.8
        product('CRIT-003', stock: 1, minimum: 10),  // ratio 0.1 (most critical)
    ]);

    $critical = (new StockCalculator)->getCriticalProducts($products);
    $skus = array_map(fn (Product $p): string => (string) $p->sku(), $critical->toArray());

    expect($critical->count())->toBe(2)
        ->and($skus)->toBe(['CRIT-003', 'MILD-002']);
});

it('builds domain events that expose when they occurred', function (): void {
    $p = product('EVT-001', stock: 1, minimum: 5);

    foreach ([
        new ProductCreated($p),
        new LowStockDetected($p, StockQuantity::of(5)),
    ] as $event) {
        expect($event)->toBeInstanceOf(DomainEvent::class)
            ->and($event->occurredOn())->toBeInstanceOf(DateTimeImmutable::class);
    }
});
