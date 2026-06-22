<?php

declare(strict_types=1);

use Stockr\Domain\Auth\ValueObjects\Email;
use Stockr\Domain\Auth\ValueObjects\WorkspaceSlug;
use Stockr\Domain\Inventory\Exceptions\InvalidSkuException;
use Stockr\Domain\Inventory\ValueObjects\Money;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;

it('normalises and validates a SKU', function (): void {
    expect((string) ProductSku::fromString(' abc-123 '))->toBe('ABC-123');
});

it('rejects an invalid SKU', function (): void {
    ProductSku::fromString('!!');
})->throws(InvalidSkuException::class);

it('generates a zero-padded SKU from a category code and sequence', function (): void {
    expect((string) ProductSku::generate('cool', 1))->toBe('COOL-001');
});

it('stores money as integer cents without float drift', function (): void {
    $price = Money::of(1999);
    expect($price->toCents())->toBe(1999)
        ->and($price->multiply(3)->toCents())->toBe(5997)
        ->and($price->toReais())->toBe('R$ 19,99');
});

it('parses a formatted BRL string into cents', function (): void {
    expect(Money::ofReais('R$ 1.234,56')->toCents())->toBe(123456)
        ->and(Money::ofReais('1234.56')->toCents())->toBe(123456);
});

it('lowercases an email', function (): void {
    expect((string) new Email('USER@Example.COM'))->toBe('user@example.com');
});

it('derives a slug from a workspace name', function (): void {
    expect((string) WorkspaceSlug::fromName('Jeito Frio Matriz'))->toBe('jeito-frio-matriz');
});
