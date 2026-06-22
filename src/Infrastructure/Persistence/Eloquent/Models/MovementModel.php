<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * Pure persistence mapping for the `movements` table. Movements are immutable
 * by design: once written they may never be updated. Only creation (insert) is
 * permitted; any attempt to mutate an existing row raises a LogicException.
 * The table has no `updated_at` column — records are write-once.
 *
 * @property int $id
 * @property int $workspace_id
 * @property string $product_id
 * @property int $user_id
 * @property string $type
 * @property int $quantity
 * @property int $quantity_before
 * @property int $quantity_after
 * @property string|null $notes
 * @property string|null $reference_code
 * @property Carbon $moved_at
 * @property Carbon $created_at
 */
final class MovementModel extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'movements';

    protected $fillable = [
        'workspace_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'notes',
        'reference_code',
        'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'string',
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'moved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    /**
     * Allow the initial insert (used by create()); forbid any later update.
     *
     * @param  array<string, mixed>  $options
     */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new LogicException('Movements are immutable.');
        }

        return parent::save($options);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $options
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new LogicException('Movements are immutable.');
    }
}
