<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\ValueObjects;

/**
 * Lifecycle status of a product in the catalogue.
 */
enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Discontinued = 'discontinued';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
            self::Discontinued => 'Descontinuado',
        };
    }

    public function isSellable(): bool
    {
        return $this === self::Active;
    }
}
