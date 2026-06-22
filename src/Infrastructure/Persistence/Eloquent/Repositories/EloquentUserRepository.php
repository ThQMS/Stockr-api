<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Repositories;

use Stockr\Domain\Auth\Entities\User;
use Stockr\Domain\Auth\Repositories\UserRepositoryInterface;
use Stockr\Domain\Auth\ValueObjects\Email;
use Stockr\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $model = UserModel::query()->with('workspaces')->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::query()->with('workspaces')->where('email', (string) $email)->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function create(User $user, string $hashedPassword): User
    {
        $model = new UserModel;
        $model->forceFill([
            'name' => $user->name(),
            'email' => (string) $user->email(),
            'password' => $hashedPassword,
        ]);
        $model->save();

        return $this->toDomain($model->fresh('workspaces') ?? $model);
    }

    private function toDomain(UserModel $model): User
    {
        /** @var list<int> $workspaceIds */
        $workspaceIds = $model->relationLoaded('workspaces')
            ? $model->workspaces->pluck('id')->map(fn ($id): int => (int) $id)->all()
            : [];

        return new User(
            id: $model->id,
            name: $model->name,
            email: new Email($model->email),
            workspaceIds: $workspaceIds,
        );
    }
}
