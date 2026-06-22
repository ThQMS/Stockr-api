<?php

declare(strict_types=1);

namespace Stockr\Application\Inventory\UseCases;

use Stockr\Domain\Auth\Exceptions\UnauthorizedWorkspaceException;
use Stockr\Domain\Inventory\Contracts\QrCodeGeneratorInterface;
use Stockr\Domain\Inventory\Exceptions\ProductNotFoundException;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;

/**
 * Produces a scannable QR code that encodes a product's identity. The encoded
 * payload is consumed back by ScanProductUseCase.
 */
final readonly class GenerateProductQrCodeUseCase
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private QrCodeGeneratorInterface $qrCode,
    ) {}

    /**
     * @return array{payload: string, dataUri: string}
     */
    public function execute(int $workspaceId, string $productId, int $size = 300): array
    {
        $product = $this->products->findById($productId)
            ?? throw ProductNotFoundException::forId($productId);

        if ($product->getWorkspaceId() !== $workspaceId) {
            throw new UnauthorizedWorkspaceException;
        }

        $payload = sprintf('stockr://product/%d/%s', $workspaceId, (string) $product->sku());

        return [
            'payload' => $payload,
            'dataUri' => $this->qrCode->generateDataUri($payload, $size),
        ];
    }
}
