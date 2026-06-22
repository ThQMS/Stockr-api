<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\DTOs;

/**
 * Typed input for registering a stock movement. Framework-free: the mapping
 * from the HTTP request lives in the Presentation layer, so Application never
 * depends on Illuminate.
 */
final readonly class RegisterMovementDTO
{
    private function __construct(
        public string $productId,
        public int $workspaceId,
        public int $userId,
        public string $type,
        public int $quantity,
        public ?string $notes,
        public ?string $referenceCode,
    ) {}

    public static function from(
        string $productId,
        int $workspaceId,
        int $userId,
        string $type,
        int $quantity,
        ?string $notes = null,
        ?string $referenceCode = null,
    ): self {
        return new self($productId, $workspaceId, $userId, $type, $quantity, $notes, $referenceCode);
    }
}
