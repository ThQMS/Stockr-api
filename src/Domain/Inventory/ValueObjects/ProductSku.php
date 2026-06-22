<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\ValueObjects;

use Stockr\Domain\Inventory\Exceptions\InvalidSkuException;
use Stringable;

/**
 * Stock Keeping Unit: an uppercase category code, a hyphen and a numeric
 * sequence, e.g. "COOL-001". Immutable and self-validating.
 */
final class ProductSku implements Stringable
{
    private const PATTERN = '/^[A-Z]{2,6}-\d{3,6}$/';

    private function __construct(
        private readonly string $value,
    ) {
        if (preg_match(self::PATTERN, $value) !== 1) {
            throw InvalidSkuException::forValue($value);
        }
    }

    /**
     * Hydrate from an existing SKU string (e.g. read from persistence). The
     * value is upper-cased and trimmed before validation.
     */
    public static function fromString(string $value): self
    {
        return new self(strtoupper(trim($value)));
    }

    /**
     * Build a SKU from a category code and a sequence number, zero-padded to at
     * least three digits, e.g. generate('cool', 1) => "COOL-001".
     */
    public static function generate(string $categoryCode, int $sequence): self
    {
        return new self(sprintf('%s-%03d', strtoupper(trim($categoryCode)), $sequence));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
