<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Contracts;

/**
 * Port for caching computed inventory reads, scoped per workspace. Implemented
 * in Infrastructure (e.g. RedisInventoryCache) with keys prefixed by workspace
 * so a single call can invalidate everything cached for that tenant.
 */
interface InventoryCacheInterface
{
    /**
     * @return array<mixed>|null
     */
    public function getProducts(string $workspaceId): ?array;

    /**
     * @param  array<mixed>  $products
     */
    public function setProducts(string $workspaceId, array $products, int $ttlSeconds = 300): void;

    /**
     * @return array<string, mixed>|null
     */
    public function getReport(string $workspaceId): ?array;

    /**
     * @param  array<string, mixed>  $report
     */
    public function setReport(string $workspaceId, array $report, int $ttlSeconds = 300): void;

    /**
     * Drop every cached entry belonging to the workspace.
     */
    public function invalidate(string $workspaceId): void;
}
