<?php

declare(strict_types=1);

namespace App\Policies;

use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;
use Stockr\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Stockr\Infrastructure\Persistence\Eloquent\Models\WorkspaceModel;

/**
 * Authorizes stock movement actions by workspace membership.
 *
 * Note: the "cannot withdraw more than available stock" rule is a domain
 * invariant enforced by the Product aggregate (InsufficientStockException), not
 * duplicated here — keeping the single source of truth in the domain.
 */
final readonly class MovementPolicy
{
    public function __construct(private WorkspaceRepositoryInterface $workspaces) {}

    public function viewAny(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->isMember($user, $workspace);
    }

    public function create(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->isMember($user, $workspace);
    }

    private function isMember(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->workspaces->isMember((int) $workspace->id, (int) $user->id);
    }
}
