<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stockr\Infrastructure\Persistence\Eloquent\Models\MovementModel;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Stockr\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Stockr\Infrastructure\Persistence\Eloquent\Models\WorkspaceModel;
use Symfony\Component\Uid\Ulid;

uses(RefreshDatabase::class);

function seedMovement(): MovementModel
{
    $user = UserModel::factory()->create();

    $workspace = WorkspaceModel::query()->create([
        'name' => 'WS', 'slug' => 'ws-'.uniqid(), 'owner_id' => $user->id,
    ]);

    $product = ProductModel::query()->create([
        'id' => (string) Ulid::generate(),
        'workspace_id' => $workspace->id,
        'sku' => 'IMU-001',
        'name' => 'Immutable',
        'cost_price_cents' => 1000,
        'current_stock' => 5,
        'minimum_stock' => 1,
        'status' => 'active',
    ]);

    return MovementModel::query()->create([
        'workspace_id' => $workspace->id,
        'product_id' => $product->id,
        'user_id' => $user->id,
        'type' => 'in',
        'quantity' => 5,
        'quantity_before' => 0,
        'quantity_after' => 5,
        'moved_at' => now(),
    ]);
}

it('allows creating a movement', function (): void {
    expect(seedMovement()->exists)->toBeTrue();
});

it('forbids updating a persisted movement via save()', function (): void {
    $movement = seedMovement();
    $movement->quantity = 99;
    $movement->save();
})->throws(LogicException::class, 'Movements are immutable.');

it('forbids update() on a movement', function (): void {
    seedMovement()->update(['quantity' => 99]);
})->throws(LogicException::class, 'Movements are immutable.');
