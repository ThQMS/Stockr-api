<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Entities;

use DateTimeImmutable;
use Stockr\Domain\Inventory\Events\LowStockDetected;
use Stockr\Domain\Inventory\Events\ProductCreated;
use Stockr\Domain\Inventory\Events\StockMovementRegistered;
use Stockr\Domain\Inventory\Exceptions\InsufficientStockException;
use Stockr\Domain\Inventory\ValueObjects\Money;
use Stockr\Domain\Inventory\ValueObjects\MovementType;
use Stockr\Domain\Inventory\ValueObjects\ProductSku;
use Stockr\Domain\Inventory\ValueObjects\ProductStatus;
use Stockr\Domain\Inventory\ValueObjects\StockQuantity;
use Stockr\Domain\Shared\DomainEvent;
use Symfony\Component\Uid\Ulid;

/**
 * Aggregate root of the Inventory context. Owns its on-hand stock and the
 * invariants that protect it. Holds no framework dependency whatsoever.
 */
final class Product
{
    /**
     * Domain events recorded by behaviour on this aggregate, awaiting dispatch.
     *
     * @var list<DomainEvent>
     */
    private array $domainEvents = [];

    public function __construct(
        public readonly ?string $id,
        public readonly int $workspaceId,
        private ProductSku $sku,
        private string $name,
        private Money $price,
        private StockQuantity $stock,
        private StockQuantity $reorderLevel,
        public readonly ?int $categoryId = null,
        private ?string $description = null,
        private ProductStatus $status = ProductStatus::Active,
        private ?Money $salePrice = null,
        private ?string $barcode = null,
        private string $unit = 'un',
        private ?string $qrCodePath = null,
    ) {}

    /**
     * Named constructor for a brand-new product: assigns a fresh ULID identity
     * and records the ProductCreated event.
     */
    public static function create(
        int $workspaceId,
        ProductSku $sku,
        string $name,
        Money $price,
        StockQuantity $stock,
        StockQuantity $reorderLevel,
        ?int $categoryId = null,
        ?string $description = null,
        ProductStatus $status = ProductStatus::Active,
        ?Money $salePrice = null,
        ?string $barcode = null,
        string $unit = 'un',
        ?string $qrCodePath = null,
    ): self {
        $product = new self(
            id: (string) new Ulid,
            workspaceId: $workspaceId,
            sku: $sku,
            name: $name,
            price: $price,
            stock: $stock,
            reorderLevel: $reorderLevel,
            categoryId: $categoryId,
            description: $description,
            status: $status,
            salePrice: $salePrice,
            barcode: $barcode,
            unit: $unit,
            qrCodePath: $qrCodePath,
        );

        $product->recordThat(new ProductCreated($product));

        return $product;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getWorkspaceId(): int
    {
        return $this->workspaceId;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): ProductSku
    {
        return $this->sku;
    }

    public function getSalePrice(): Money
    {
        return $this->salePrice ?? Money::zero();
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getQrCodePath(): ?string
    {
        return $this->qrCodePath;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function sku(): ProductSku
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function stock(): StockQuantity
    {
        return $this->stock;
    }

    public function reorderLevel(): StockQuantity
    {
        return $this->reorderLevel;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    // --- Intention-revealing aliases used by domain services ----------------
    // The model carries a single unit price, which doubles as the cost basis
    // for inventory valuation, and the reorder level is the minimum stock.

    public function getCostPrice(): Money
    {
        return $this->price;
    }

    public function getCurrentStock(): StockQuantity
    {
        return $this->stock;
    }

    public function getMinimumStock(): StockQuantity
    {
        return $this->reorderLevel;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changePrice(Money $price): void
    {
        $this->price = $price;
    }

    public function changeReorderLevel(StockQuantity $reorderLevel): void
    {
        $this->reorderLevel = $reorderLevel;
    }

    public function describe(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Register a stock movement against this product: apply it to the on-hand
     * stock (enforcing invariants), build the Movement, and record the domain
     * events the change produced. The Movement is returned so the caller can
     * persist it; its identity is assigned by the repository.
     */
    public function registerMovement(
        MovementType $type,
        StockQuantity $quantity,
        int $userId,
        ?string $notes = null,
        ?string $referenceCode = null,
    ): Movement {
        $quantityBefore = $this->stock->getValue();
        $this->applyMovement($type, $quantity);
        $quantityAfter = $this->stock->getValue();

        $movement = new Movement(
            id: null,
            workspaceId: $this->workspaceId,
            productId: (string) $this->id,
            userId: $userId,
            type: $type,
            quantity: $quantity,
            quantityBefore: $quantityBefore,
            quantityAfter: $quantityAfter,
            notes: $notes,
            referenceCode: $referenceCode,
            movedAt: new DateTimeImmutable,
        );

        $this->recordThat(new StockMovementRegistered($movement, $this));

        if ($this->isBelowReorderLevel()) {
            $this->recordThat(new LowStockDetected($this, $this->reorderLevel));
        }

        return $movement;
    }

    /**
     * Apply a movement to the on-hand stock, enforcing the no-negative-stock invariant.
     */
    public function applyMovement(MovementType $type, StockQuantity $quantity): void
    {
        $this->stock = match ($type) {
            MovementType::In => $this->stock->add($quantity),
            MovementType::Out, MovementType::Transfer => $this->withdraw($quantity),
            MovementType::Adjustment => $quantity,
        };
    }

    public function isBelowReorderLevel(): bool
    {
        return $this->stock->isBelow($this->reorderLevel)
            || $this->stock->equals($this->reorderLevel);
    }

    /**
     * Drain the recorded domain events, leaving the aggregate's buffer empty.
     *
     * @return list<DomainEvent>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordThat(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    private function withdraw(StockQuantity $quantity): StockQuantity
    {
        if ($this->stock->isBelow($quantity)) {
            // Re-raise with the SKU for a richer message than the bare VO error.
            throw InsufficientStockException::forProduct(
                (string) $this->sku,
                $this->stock->getValue(),
                $quantity->getValue(),
            );
        }

        return $this->stock->subtract($quantity);
    }
}
