<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Repositories;

use Stockr\Domain\Auth\Entities\Workspace;
use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;
use Stockr\Domain\Auth\ValueObjects\WorkspaceSlug;
use Stockr\Infrastructure\Persistence\Eloquent\Models\WorkspaceModel;

final class EloquentWorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function findById(int $id): ?Workspace
    {
        $model = WorkspaceModel::query()->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findBySlug(WorkspaceSlug $slug): ?Workspace
    {
        $model = WorkspaceModel::query()->where('slug', (string) $slug)->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function save(Workspace $workspace): Workspace
    {
        $model = $workspace->id !== null
            ? WorkspaceModel::query()->findOrNew($workspace->id)
            : new WorkspaceModel;

        $model->fill([
            'name' => $workspace->name(),
            'slug' => (string) $workspace->slug(),
            'owner_id' => $workspace->ownerId,
        ]);
        $model->save();

        // The owner is always a member of their workspace.
        $model->members()->syncWithoutDetaching([$workspace->ownerId]);

        return $this->toDomain($model);
    }

    public function isMember(int $workspaceId, int $userId): bool
    {
        return WorkspaceModel::query()
            ->where('id', $workspaceId)
            ->whereHas('members', fn ($q) => $q->where('users.id', $userId))
            ->exists();
    }

    public function forUser(int $userId): array
    {
        return array_values(
            WorkspaceModel::query()
                ->whereHas('members', fn ($q) => $q->where('users.id', $userId))
                ->orderBy('name')
                ->get()
                ->map(fn (WorkspaceModel $m): Workspace => $this->toDomain($m))
                ->all()
        );
    }

    private function toDomain(WorkspaceModel $model): Workspace
    {
        return new Workspace(
            id: $model->id,
            name: $model->name,
            slug: WorkspaceSlug::fromString($model->slug),
            ownerId: $model->owner_id,
        );
    }
}
