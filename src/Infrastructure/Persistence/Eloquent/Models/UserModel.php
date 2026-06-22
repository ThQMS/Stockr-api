<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Stockr\Domain\Auth\Entities\User;

/**
 * Eloquent mapping for the `users` table and the Sanctum token-bearing identity.
 * Authentication plumbing only — domain rules live in {@see User}.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
final class UserModel extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(WorkspaceModel::class, 'workspace_user', 'user_id', 'workspace_id');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
