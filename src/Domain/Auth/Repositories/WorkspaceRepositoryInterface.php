<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Repositories;

use Stockr\Domain\Auth\Entities\Workspace;
use Stockr\Domain\Auth\ValueObjects\WorkspaceSlug;

interface WorkspaceRepositoryInterface
{
    public function findById(int $id): ?Workspace;

    public function findBySlug(WorkspaceSlug $slug): ?Workspace;

    public function save(Workspace $workspace): Workspace;

    public function isMember(int $workspaceId, int $userId): bool;

    /**
     * @return list<Workspace>
     */
    public function forUser(int $userId): array;
}
