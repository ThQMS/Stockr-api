<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Stockr\Domain\Inventory\Entities\Movement;
use Stockr\Domain\Inventory\Entities\Product;
use Stockr\Domain\Inventory\Repositories\MovementRepositoryInterface;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Inventory\ValueObjects\MovementType;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;
use Stockr\Infrastructure\Persistence\Eloquent\Models\MovementModel;

final class EloquentMovementRepository implements MovementRepositoryInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    public function save(Movement $movement): Movement
    {
        // Movements are immutable: only create() (a pure insert) is permitted.
        $model = MovementModel::create([
            'workspace_id' => $movement->workspaceId,
            'product_id' => $movement->productId,
            'user_id' => $movement->userId,
            'type' => $movement->type->value,
            'quantity' => $movement->quantity->getValue(),
            'quantity_before' => $movement->quantityBefore,
            'quantity_after' => $movement->quantityAfter,
            'notes' => $movement->notes,
            'reference_code' => $movement->referenceCode,
            'moved_at' => $movement->movedAt->format('Y-m-d H:i:s'),
        ]);

        return $this->toDomain($model);
    }

    public function saveWithProduct(Movement $movement, Product $product): Movement
    {
        return DB::transaction(function () use ($movement, $product): Movement {
            $this->products->save($product);

            return $this->save($movement);
        });
    }

    public function forProduct(string $productId, int $workspaceId): array
    {
        return array_values(
            MovementModel::query()
                ->where('product_id', $productId)
                ->where('workspace_id', $workspaceId)
                ->orderByDesc('moved_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn (MovementModel $m): Movement => $this->toDomain($m))
                ->all()
        );
    }

    public function recentForProduct(string $productId, int $limit = 5): array
    {
        return array_values(
            MovementModel::query()
                ->where('product_id', $productId)
                ->orderByDesc('moved_at')
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->map(fn (MovementModel $m): Movement => $this->toDomain($m))
                ->all()
        );
    }

    public function betweenDates(int $workspaceId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return array_values(
            MovementModel::query()
                ->where('workspace_id', $workspaceId)
                ->whereBetween('moved_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
                ->orderBy('moved_at')
                ->get()
                ->map(fn (MovementModel $m): Movement => $this->toDomain($m))
                ->all()
        );
    }

    private function toDomain(MovementModel $model): Movement
    {
        return new Movement(
            id: $model->id,
            workspaceId: $model->workspace_id,
            productId: $model->product_id,
            userId: $model->user_id,
            type: MovementType::from($model->type),
            quantity: StockQuantity::of($model->quantity),
            quantityBefore: $model->quantity_before,
            quantityAfter: $model->quantity_after,
            notes: $model->notes,
            referenceCode: $model->reference_code,
            movedAt: new DateTimeImmutable($model->moved_at->toDateTimeString()),
        );
    }
}
