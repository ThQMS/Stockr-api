<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Stockr\Domain\Inventory\Entities\Movement;

/**
 * @property-read Movement $resource
 *
 * @mixin Movement
 */
final class MovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $movement = $this->resource;

        return [
            'id' => $movement->id,
            'workspace_id' => $movement->workspaceId,
            'product_id' => $movement->productId,
            'user_id' => $movement->userId,
            'type' => $movement->type->value,
            'type_label' => $movement->type->label(),
            'quantity' => $movement->quantity->getValue(),
            'signed_quantity' => $movement->signedQuantity(),
            'quantity_before' => $movement->quantityBefore,
            'quantity_after' => $movement->quantityAfter,
            'notes' => $movement->notes,
            'reference_code' => $movement->referenceCode,
            'moved_at' => $movement->movedAt->format(DATE_ATOM),
        ];
    }
}
