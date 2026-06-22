<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active workspace from the `X-Workspace-Id` header (or `workspace`
 * route parameter) and guarantees the authenticated user belongs to it. On
 * success the validated id is stashed as the `workspaceId` request attribute.
 */
final readonly class EnsureWorkspaceMember
{
    public function __construct(private WorkspaceRepositoryInterface $workspaces) {}

    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) $request->user()?->getAuthIdentifier();

        $routeWorkspace = $request->route('workspace');
        $rawWorkspaceId = is_scalar($routeWorkspace)
            ? (string) $routeWorkspace
            : (string) ($request->header('X-Workspace-Id') ?? '0');
        $workspaceId = (int) $rawWorkspaceId;

        if ($workspaceId <= 0) {
            abort(Response::HTTP_BAD_REQUEST, 'Missing workspace context (X-Workspace-Id header).');
        }

        if (! $this->workspaces->isMember($workspaceId, $userId)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not a member of this workspace.');
        }

        $request->attributes->set('workspaceId', $workspaceId);

        return $next($request);
    }
}
