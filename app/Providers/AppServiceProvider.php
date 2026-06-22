<?php

declare(strict_types=1);

namespace App\Providers;

use App\Policies\MovementPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SimpleSoftwareIO\QrCode\Generator;
use Stockr\Domain\Auth\Contracts\CredentialVerifierInterface;
use Stockr\Domain\Auth\Contracts\PasswordHasherInterface;
use Stockr\Domain\Auth\Contracts\TokenIssuerInterface;
use Stockr\Domain\Auth\Repositories\UserRepositoryInterface;
use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;
use Stockr\Domain\Inventory\Contracts\InventoryCacheInterface;
use Stockr\Domain\Inventory\Contracts\QrCodeGeneratorInterface;
use Stockr\Domain\Inventory\Repositories\MovementRepositoryInterface;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Domain\Shared\EventDispatcherInterface;
use Stockr\Infrastructure\Auth\EloquentCredentialVerifier;
use Stockr\Infrastructure\Auth\LaravelPasswordHasher;
use Stockr\Infrastructure\Auth\SanctumTokenIssuer;
use Stockr\Infrastructure\Cache\RedisInventoryCache;
use Stockr\Infrastructure\Events\LaravelEventDispatcherAdapter;
use Stockr\Infrastructure\Persistence\Eloquent\Models\MovementModel;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Stockr\Infrastructure\Persistence\Eloquent\Repositories\EloquentMovementRepository;
use Stockr\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Stockr\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use Stockr\Infrastructure\Persistence\Eloquent\Repositories\EloquentWorkspaceRepository;
use Stockr\Infrastructure\QrCode\SimpleSoftwareQrCodeAdapter;

/**
 * Composition root. Wires the Domain's ports to their Infrastructure adapters so
 * the inner layers never reference a concrete framework class directly.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Straightforward interface → implementation bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        // Inventory repositories
        ProductRepositoryInterface::class => EloquentProductRepository::class,
        MovementRepositoryInterface::class => EloquentMovementRepository::class,
        // Auth repositories
        UserRepositoryInterface::class => EloquentUserRepository::class,
        WorkspaceRepositoryInterface::class => EloquentWorkspaceRepository::class,
        // Auth ports
        PasswordHasherInterface::class => LaravelPasswordHasher::class,
        CredentialVerifierInterface::class => EloquentCredentialVerifier::class,
        TokenIssuerInterface::class => SanctumTokenIssuer::class,
        // Shared
        EventDispatcherInterface::class => LaravelEventDispatcherAdapter::class,
    ];

    public function register(): void
    {
        // Bind the Hasher port to the configured driver.
        $this->app->when([LaravelPasswordHasher::class, EloquentCredentialVerifier::class])
            ->needs(Hasher::class)
            ->give(fn () => $this->app->make('hash')->driver());

        // Inventory cache backed by the Redis store.
        $this->app->bind(InventoryCacheInterface::class, function (): RedisInventoryCache {
            /** @var CacheFactory $cache */
            $cache = $this->app->make(CacheFactory::class);

            // Prefer the dedicated inventory store; fall back to redis, then to
            // whatever the app default is so the binding resolves out of the box.
            $store = config('cache.inventory_store')
                ?? (config('cache.stores.redis') !== null ? 'redis' : config('cache.default'));

            return new RedisInventoryCache($cache->store($store));
        });

        // QR code adapter.
        $this->app->bind(
            QrCodeGeneratorInterface::class,
            fn (): SimpleSoftwareQrCodeAdapter => new SimpleSoftwareQrCodeAdapter(new Generator),
        );
    }

    public function boot(): void
    {
        // Migrations live inside the Infrastructure layer, not database/migrations.
        $this->loadMigrationsFrom(base_path('src/Infrastructure/Persistence/Migrations'));

        // Authorization policies (workspace-membership based).
        Gate::policy(ProductModel::class, ProductPolicy::class);
        Gate::policy(MovementModel::class, MovementPolicy::class);
    }
}
