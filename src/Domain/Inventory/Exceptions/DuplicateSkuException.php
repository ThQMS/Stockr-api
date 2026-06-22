<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Exceptions;

use DomainException;

final class DuplicateSkuException extends DomainException
{
    public static function inWorkspace(string $sku, int $workspaceId): self
    {
        return new self(sprintf('SKU "%s" already exists in workspace %d.', $sku, $workspaceId));
    }
}
