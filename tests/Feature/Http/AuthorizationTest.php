<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeaders;

/**
 * @return array{token: string, workspaceId: int}
 */
function registerAccount(string $email, string $workspace): array
{
    $r = postJson('/api/v1/auth/register', [
        'name' => 'User',
        'email' => $email,
        'password' => 'secret123',
        'workspace_name' => $workspace,
    ])->assertCreated();

    return ['token' => $r->json('token'), 'workspaceId' => (int) $r->json('workspaceIds.0')];
}

it('forbids creating a product in a workspace the user does not belong to', function (): void {
    $alice = registerAccount('alice@example.com', 'Alice WS');
    $bob = registerAccount('bob@example.com', 'Bob WS');

    // Bob's token, but pointing at Alice's workspace → policy denial.
    withHeaders([
        'Authorization' => 'Bearer '.$bob['token'],
        'X-Workspace-Id' => (string) $alice['workspaceId'],
    ])
        ->postJson('/api/v1/products', ['name' => 'Intruder', 'cost_price' => 10])
        ->assertForbidden();
});

it('exports the inventory report as CSV', function (): void {
    $acc = registerAccount('csv@example.com', 'CSV WS');
    $headers = [
        'Authorization' => 'Bearer '.$acc['token'],
        'X-Workspace-Id' => (string) $acc['workspaceId'],
    ];

    withHeaders($headers)->postJson('/api/v1/products', [
        'sku' => 'CSV-001', 'name' => 'Exported', 'cost_price' => 12.5, 'initial_stock' => 3,
    ])->assertCreated();

    $response = withHeaders($headers)->get('/api/v1/reports/export');
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');

    $body = $response->streamedContent();
    expect($body)->toContain('sku,name,stock')
        ->and($body)->toContain('CSV-001');
});
