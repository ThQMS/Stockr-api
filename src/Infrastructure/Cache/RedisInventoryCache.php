<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Cache;

use Illuminate\Contracts\Cache\Repository as Cache;
use Stockr\Domain\Inventory\Contracts\InventoryCacheInterface;

/**
 * Redis-backed implementation of the inventory cache port. Every key lives under
 * the per-workspace prefix "stockr:{workspaceId}:", so {@see invalidate()} can
 * drop all of a tenant's cached reads in one call.
 */
final readonly class RedisInventoryCache implements InventoryCacheInterface
{
    /**
     * The set of cache segments a workspace owns. invalidate() clears them all.
     */
    private const SEGMENTS = ['products', 'report'];

    public function __construct(private Cache $cache) {}

    public function getProducts(string $workspaceId): ?array
    {
        $value = $this->cache->get($this->key($workspaceId, 'products'));

        return is_array($value) ? $value : null;
    }

    public function setProducts(string $workspaceId, array $products, int $ttlSeconds = 300): void
    {
        $this->cache->put($this->key($workspaceId, 'products'), $products, $ttlSeconds);
    }

    public function getReport(string $workspaceId): ?array
    {
        $value = $this->cache->get($this->key($workspaceId, 'report'));

        return is_array($value) ? $value : null;
    }

    public function setReport(string $workspaceId, array $report, int $ttlSeconds = 300): void
    {
        $this->cache->put($this->key($workspaceId, 'report'), $report, $ttlSeconds);
    }

    public function invalidate(string $workspaceId): void
    {
        foreach (self::SEGMENTS as $segment) {
            $this->cache->forget($this->key($workspaceId, $segment));
        }
    }

    private function prefix(string $workspaceId): string
    {
        return "stockr:{$workspaceId}:";
    }

    private function key(string $workspaceId, string $segment): string
    {
        return $this->prefix($workspaceId).$segment;
    }
}
