<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Requests\Concerns;

use Stockr\Infrastructure\Persistence\Eloquent\Models\WorkspaceModel;

/**
 * Resolves the active WorkspaceModel for the request from the context set by the
 * EnsureWorkspaceMember middleware (falling back to the X-Workspace-Id header),
 * so Form Requests can authorize against the workspace-scoped policies.
 */
trait ResolvesWorkspace
{
    protected function activeWorkspace(): ?WorkspaceModel
    {
        $id = $this->attributes->get('workspaceId')
            ?? $this->route('workspace')
            ?? $this->header('X-Workspace-Id');

        if ($id === null || (int) $id <= 0) {
            return null;
        }

        return WorkspaceModel::query()->find((int) $id);
    }
}
