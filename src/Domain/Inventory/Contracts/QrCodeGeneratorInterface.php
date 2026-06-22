<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\Contracts;

/**
 * Port for turning a product payload into a QR code. Implemented in Infrastructure
 * (e.g. SimpleSoftwareQrCodeAdapter) so the Domain stays free of vendor concerns.
 */
interface QrCodeGeneratorInterface
{
    /**
     * @return string Raw QR code image bytes (PNG or SVG depending on the adapter).
     */
    public function generate(string $payload, int $size = 300): string;

    /**
     * @return string Data URI usable directly in an <img src="..."> tag.
     */
    public function generateDataUri(string $payload, int $size = 300): string;
}
