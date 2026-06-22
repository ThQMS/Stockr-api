<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

/**
 * End-to-end happy path exercised through the public HTTP API: register,
 * create a product, register stock movements and read the inventory report.
 */
it('runs the full inventory lifecycle over the API', function (): void {
    // Register — provisions a user, a workspace and an API token.
    $register = postJson('/api/v1/auth/register', [
        'name' => 'Thiago',
        'email' => 'thiago@example.com',
        'password' => 'secret123',
        'workspace_name' => 'Jeito Frio',
    ])->assertCreated();

    $token = $register->json('token');
    $workspaceId = $register->json('workspaceIds.0');

    $headers = [
        'Authorization' => "Bearer {$token}",
        'X-Workspace-Id' => (string) $workspaceId,
    ];

    // Create a product.
    $product = withHeaders($headers)
        ->postJson('/api/v1/products', [
            'sku' => 'COOL-001',
            'name' => 'Compressor',
            'cost_price' => 1200.50,
            'initial_stock' => 10,
            'minimum_stock' => 4,
        ])
        ->assertCreated()
        ->assertJsonPath('data.sku', 'COOL-001')
        ->assertJsonPath('data.current_stock', 10);

    $productId = $product->json('data.id');

    // Register an outbound movement that crosses the reorder threshold.
    withHeaders($headers)
        ->postJson("/api/v1/products/{$productId}/movements", [
            'type' => 'out',
            'quantity' => 7,
            'notes' => 'Sale',
        ])
        ->assertCreated()
        ->assertJsonPath('resultingStock', 3)
        ->assertJsonPath('lowStockTriggered', true);

    // Inventory report reflects the new figures.
    withHeaders($headers)
        ->getJson('/api/v1/reports/summary')
        ->assertOk()
        ->assertJsonPath('totalProducts', 1)
        ->assertJsonPath('totalUnits', 3)
        ->assertJsonPath('lowStockCount', 1);
});

it('rejects access without a workspace context', function (): void {
    $register = postJson('/api/v1/auth/register', [
        'name' => 'NoWs',
        'email' => 'nows@example.com',
        'password' => 'secret123',
        'workspace_name' => 'Solo',
    ])->assertCreated();

    withHeaders(['Authorization' => 'Bearer '.$register->json('token')])
        ->getJson('/api/v1/products')
        ->assertStatus(400); // missing X-Workspace-Id
});
