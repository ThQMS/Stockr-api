<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\UseCases;

use Stockr\Domain\Auth\Exceptions\UnauthorizedWorkspaceException;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\Exceptions\ProductNotFoundException;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Inventory\ValueObjects\Money;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;

/**
 * Updates the mutable attributes of an existing product. Stock is never changed
 * here — that flows exclusively through RegisterMovementUseCase.
 */
final readonly class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    public function execute(
        int $workspaceId,
        string $productId,
        ?string $name = null,
        ?float $price = null,
        ?int $reorderLevel = null,
        ?string $description = null,
    ): Product {
        $product = $this->products->findById($productId)
            ?? throw ProductNotFoundException::forId($productId);

        if ($product->getWorkspaceId() !== $workspaceId) {
            throw new UnauthorizedWorkspaceException;
        }

        if ($name !== null) {
            $product->rename($name);
        }

        if ($price !== null) {
            $product->changePrice(Money::of((int) round($price * 100)));
        }

        if ($reorderLevel !== null) {
            $product->changeReorderLevel(StockQuantity::of($reorderLevel));
        }

        if ($description !== null) {
            $product->describe($description);
        }

        $this->products->save($product);

        return $product;
    }
}
