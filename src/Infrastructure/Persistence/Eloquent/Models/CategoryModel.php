<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $workspace_id
 * @property string $name
 * @property string|null $description
 */
final class CategoryModel extends Model
{
    protected $table = 'categories';

    protected $fillable = ['workspace_id', 'name', 'description'];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(WorkspaceModel::class, 'workspace_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'category_id');
    }
}
