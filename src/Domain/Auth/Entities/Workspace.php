<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Entities;

use Stockr\Domain\Auth\ValueObjects\WorkspaceSlug;

/**
 * A tenant boundary: every product, movement and user membership lives inside one.
 */
final class Workspace
{
    public function __construct(
        public readonly ?int $id,
        private string $name,
        private WorkspaceSlug $slug,
        public readonly int $ownerId,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): WorkspaceSlug
    {
        return $this->slug;
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->ownerId === $userId;
    }
}
