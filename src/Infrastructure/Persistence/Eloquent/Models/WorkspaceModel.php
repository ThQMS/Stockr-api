<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent mapping for the `workspaces` table. No business logic — persistence only.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $owner_id
 */
final class WorkspaceModel extends Model
{
    protected $table = 'workspaces';

    protected $fillable = ['name', 'slug', 'owner_id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'workspace_user', 'workspace_id', 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'workspace_id');
    }
}
