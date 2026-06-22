<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

/**
 * @return array{0: array<string, string>, 1: int}
 */
function authedHeaders(string $email = 'owner@example.com'): array
{
    $register = postJson('/api/v1/auth/register', [
        'name' => 'Owner',
        'email' => $email,
        'password' => 'secret123',
        'workspace_name' => 'Jeito Frio',
    ])->assertCreated();

    return [
        [
            'Authorization' => 'Bearer '.$register->json('token'),
            'X-Workspace-Id' => (string) $register->json('workspaceIds.0'),
        ],
        (int) $register->json('workspaceIds.0'),
    ];
}

it('generates a SKU when none is provided', function (): void {
    [$headers] = authedHeaders();

    withHeaders($headers)
        ->postJson('/api/v1/products', ['name' => 'Compressor Scroll', 'cost_price' => 100.00])
        ->assertCreated()
        ->assertJsonPath('data.sku', 'COMPRE-001');
});

it('rejects a duplicate SKU within the workspace', function (): void {
    [$headers] = authedHeaders();

    withHeaders($headers)->postJson('/api/v1/products', [
        'sku' => 'COOL-001', 'name' => 'A', 'cost_price' => 10,
    ])->assertCreated();

    withHeaders($headers)->postJson('/api/v1/products', [
        'sku' => 'COOL-001', 'name' => 'B', 'cost_price' => 20,
    ])->assertStatus(409);
});

it('scans a product and returns status plus recent movements', function (): void {
    [$headers] = authedHeaders();

    $productId = withHeaders($headers)->postJson('/api/v1/products', [
        'sku' => 'COOL-009', 'name' => 'Freezer', 'cost_price' => 500, 'initial_stock' => 5, 'minimum_stock' => 4,
    ])->json('data.id');

    withHeaders($headers)->postJson("/api/v1/products/{$productId}/movements", [
        'type' => 'out', 'quantity' => 2, 'notes' => 'Sale',
    ])->assertCreated();

    withHeaders($headers)
        ->postJson('/api/v1/products/scan', ['code' => 'COOL-009'])
        ->assertOk()
        ->assertJsonPath('sku', 'COOL-009')
        ->assertJsonPath('stock', 3)
        ->assertJsonPath('stockStatus', 'low')
        ->assertJsonPath('recentMovements.0.type', 'out')
        ->assertJsonPath('recentMovements.0.notes', 'Sale');
});
