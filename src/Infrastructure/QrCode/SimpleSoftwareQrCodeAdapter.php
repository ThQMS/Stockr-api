<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\QrCode;

use SimpleSoftwareIO\QrCode\Generator;
use Stockr\Domain\Inventory\Contracts\QrCodeGeneratorInterface;

/**
 * Adapts the simplesoftwareio/simple-qrcode generator to the Domain's QR port.
 * Emits SVG so no imagick/gd dependency is required at runtime.
 */
final readonly class SimpleSoftwareQrCodeAdapter implements QrCodeGeneratorInterface
{
    public function __construct(private Generator $generator) {}

    public function generate(string $payload, int $size = 300): string
    {
        $svg = $this->generator
            ->format('svg')
            ->size($size)
            ->margin(1)
            ->generate($payload);

        // generate() returns string|HtmlString (a Stringable) for the SVG writer.
        return (string) $svg;
    }

    public function generateDataUri(string $payload, int $size = 300): string
    {
        $svg = $this->generate($payload, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
