<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\ValueObjects;

/**
 * The kind of stock movement.
 */
enum MovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Entrada',
            self::Out => 'Saída',
            self::Adjustment => 'Ajuste',
            self::Transfer => 'Transferência',
        };
    }

    /**
     * Whether the movement adds units to the on-hand stock. Only inbound
     * movements do; outbound and transfers reduce it, and adjustments set an
     * absolute correction rather than a signed delta.
     */
    public function affectsStockPositively(): bool
    {
        return $this === self::In;
    }
}
