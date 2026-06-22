<?php

declare(strict_types=1);

namespace Stockr\Application\Auth\UseCases;

use Stockr\Domain\Auth\Entities\Workspace;
use Stockr\Domain\Auth\Exceptions\UnauthorizedWorkspaceException;
use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;

/**
 * Confirms a user may act inside a workspace and returns it as the active tenant.
 */
final readonly class SelectWorkspaceUseCase
{
    public function __construct(
        private WorkspaceRepositoryInterface $workspaces,
    ) {}

    public function execute(int $userId, int $workspaceId): Workspace
    {
        if (! $this->workspaces->isMember($workspaceId, $userId)) {
            throw UnauthorizedWorkspaceException::forUser($userId, $workspaceId);
        }

        $workspace = $this->workspaces->findById($workspaceId);

        if ($workspace === null) {
            throw UnauthorizedWorkspaceException::forUser($userId, $workspaceId);
        }

        return $workspace;
    }
}
