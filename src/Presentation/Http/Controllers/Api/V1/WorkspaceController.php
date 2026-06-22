<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Stockr\Application\Auth\UseCases\SelectWorkspaceUseCase;
use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;
use Stockr\Presentation\Http\Requests\SelectWorkspaceRequest;
use Stockr\Presentation\Http\Resources\WorkspaceResource;

#[Prefix('api/v1/workspaces')]
#[Middleware('auth:sanctum')]
final class WorkspaceController
{
    #[Get('/', name: 'workspaces.index')]
    public function index(Request $request, WorkspaceRepositoryInterface $workspaces): AnonymousResourceCollection
    {
        $owned = $workspaces->forUser((int) $request->user()?->getAuthIdentifier());

        return WorkspaceResource::collection($owned);
    }

    #[Post('select', name: 'workspaces.select')]
    public function select(SelectWorkspaceRequest $request, SelectWorkspaceUseCase $useCase): WorkspaceResource
    {
        $workspace = $useCase->execute(
            (int) $request->user()?->getAuthIdentifier(),
            $request->integer('workspace_id'),
        );

        return new WorkspaceResource($workspace);
    }

    #[Get('{workspace}', name: 'workspaces.show')]
    public function show(Request $request, int $workspace, SelectWorkspaceUseCase $useCase): WorkspaceResource
    {
        $resolved = $useCase->execute((int) $request->user()?->getAuthIdentifier(), $workspace);

        return new WorkspaceResource($resolved);
    }
}
