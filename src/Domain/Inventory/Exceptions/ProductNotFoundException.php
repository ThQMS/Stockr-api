<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Exceptions;

use RuntimeException;

final class ProductNotFoundException extends RuntimeException
{
    public function __construct(string $message, public readonly ?string $productId = null)
    {
        parent::__construct($message);
    }

    public static function forId(string $id): self
    {
        return new self(sprintf('Product %s was not found.', $id), $id);
    }

    public static function forSku(string $sku): self
    {
        return new self(sprintf('No product matches "%s".', $sku));
    }
}
