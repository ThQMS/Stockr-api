<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Repositories;

use DateTimeImmutable;
use Stockr\Domain\Inventory\Entities\Movement;
use Stockr\Domain\Inventory\Entities\Product;

interface MovementRepositoryInterface
{
    public function save(Movement $movement): Movement;

    /**
     * Persist a movement and its product's new state atomically (single
     * transaction). Returns the movement with its assigned identity.
     */
    public function saveWithProduct(Movement $movement, Product $product): Movement;

    /**
     * @return list<Movement>
     */
    public function forProduct(string $productId, int $workspaceId): array;

    /**
     * The most recent movements for a product, newest first.
     *
     * @return list<Movement>
     */
    public function recentForProduct(string $productId, int $limit = 5): array;

    /**
     * @return list<Movement>
     */
    public function betweenDates(
        int $workspaceId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
    ): array;
}
