<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Entities;

/**
 * A product category, scoped to a workspace.
 */
final class Category
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $workspaceId,
        public string $name,
        public ?string $description = null,
    ) {}

    public function rename(string $name): void
    {
        $this->name = $name;
    }
}
