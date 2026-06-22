<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Collections;

use Stockr\Domain\Inventory\Entities\Product;

/**
 * An immutable, typed collection of Product aggregates. Keeps the domain free
 * of framework collection classes while giving services a first-class type to
 * operate over.
 */
final class ProductCollection
{
    /**
     * @param  list<Product>  $products
     */
    private function __construct(
        private readonly array $products,
    ) {}

    /**
     * @param  iterable<Product>  $products
     */
    public static function fromArray(iterable $products): self
    {
        $items = [];

        foreach ($products as $product) {
            $items[] = $product;
        }

        return new self($items);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<Product>
     */
    public function toArray(): array
    {
        return $this->products;
    }

    public function count(): int
    {
        return count($this->products);
    }

    public function isEmpty(): bool
    {
        return $this->products === [];
    }
}
