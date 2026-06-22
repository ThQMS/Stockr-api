<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\UseCases;

use Stockr\Application\Inventory\DTOs\MovementResultDTO;
use Stockr\Application\Inventory\DTOs\RegisterMovementDTO;
use Stockr\Domain\Auth\Exceptions\UnauthorizedWorkspaceException;
use Stockr\Domain\Inventory\Contracts\InventoryCacheInterface;
use Stockr\Domain\Inventory\Exceptions\ProductNotFoundException;
use Stockr\Domain\Inventory\Repositories\MovementRepositoryInterface;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Inventory\ValueObjects\MovementType;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;
use Stockr\Domain\Shared\EventDispatcherInterface;

/**
 * Registers a stock movement. All business logic lives on the Product
 * aggregate; this use case only loads, authorizes, persists atomically and
 * publishes the events the aggregate recorded.
 */
final readonly class RegisterMovementUseCase
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private MovementRepositoryInterface $movements,
        private InventoryCacheInterface $cache,
        private EventDispatcherInterface $events,
    ) {}

    public function execute(RegisterMovementDTO $dto): MovementResultDTO
    {
        $product = $this->products->findById($dto->productId)
            ?? throw ProductNotFoundException::forId($dto->productId);

        if ($product->getWorkspaceId() !== $dto->workspaceId) {
            throw new UnauthorizedWorkspaceException;
        }

        // All invariants (no negative stock, low-stock detection) live in the entity.
        $movement = $product->registerMovement(
            type: MovementType::from($dto->type),
            quantity: StockQuantity::of($dto->quantity),
            userId: $dto->userId,
            notes: $dto->notes,
            referenceCode: $dto->referenceCode,
        );

        // Repository persists movement and the product's new state in one transaction.
        $movement = $this->movements->saveWithProduct($movement, $product);

        // Stock figures changed: drop the workspace's cached reads.
        $this->cache->invalidate((string) $dto->workspaceId);

        foreach ($product->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }

        return MovementResultDTO::fromMovementAndProduct($movement, $product);
    }
}
