<?php

declare(strict_types=1);

namespace App\Policies;

use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;
use Stockr\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Stockr\Infrastructure\Persistence\Eloquent\Models\WorkspaceModel;

/**
 * Authorizes product actions. Every ability resolves to a single rule: the user
 * must be a member of the workspace the action targets. The product↔workspace
 * ownership invariant is additionally enforced inside the use cases.
 *
 * Invoked via Gate, e.g. $user->can('create', [ProductModel::class, $workspace]).
 */
final readonly class ProductPolicy
{
    public function __construct(private WorkspaceRepositoryInterface $workspaces) {}

    public function viewAny(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->isMember($user, $workspace);
    }

    public function view(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->isMember($user, $workspace);
    }

    public function create(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->isMember($user, $workspace);
    }

    public function update(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->isMember($user, $workspace);
    }

    public function delete(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->isMember($user, $workspace);
    }

    private function isMember(UserModel $user, WorkspaceModel $workspace): bool
    {
        return $this->workspaces->isMember((int) $workspace->id, (int) $user->id);
    }
}
