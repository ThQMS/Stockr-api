<?php

declare(strict_types=1);

namespace Stockr\Domain\Inventory\ValueObjects;

use InvalidArgumentException;

/**
 * A monetary amount in Brazilian Real, stored exclusively as integer cents to
 * avoid floating-point drift. Immutable; arithmetic returns new instances.
 */
final class Money
{
    private function __construct(
        private readonly int $cents,
    ) {}

    public static function of(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Build from a human-formatted BRL string such as "R$ 1.234,56", "1.234,56"
     * or "1234.56". The thousands separator (".") is optional and the decimal
     * separator may be "," or ".".
     */
    public static function ofReais(string $formatted): self
    {
        $normalized = trim(str_replace(['R$', ' ', "\u{00A0}"], '', $formatted));

        if ($normalized === '') {
            throw new InvalidArgumentException('Cannot parse an empty monetary value.');
        }

        // Treat the last separator as the decimal point, anything before as thousands.
        $lastComma = strrpos($normalized, ',');
        $lastDot = strrpos($normalized, '.');
        $decimalPos = max($lastComma === false ? -1 : $lastComma, $lastDot === false ? -1 : $lastDot);

        if ($decimalPos === -1) {
            $integerPart = preg_replace('/\D/', '', $normalized) ?? '';
            $fraction = '00';
        } else {
            $integerPart = preg_replace('/\D/', '', substr($normalized, 0, $decimalPos)) ?? '';
            $fraction = str_pad(preg_replace('/\D/', '', substr($normalized, $decimalPos + 1)) ?? '', 2, '0');
            $fraction = substr($fraction, 0, 2);
        }

        $integerPart = $integerPart === '' ? '0' : $integerPart;

        return new self(((int) $integerPart) * 100 + (int) $fraction);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function multiply(int $factor): self
    {
        return new self($this->cents * $factor);
    }

    public function toCents(): int
    {
        return $this->cents;
    }

    /**
     * Format as a BRL string, e.g. "R$ 1.234,56" (negatives as "-R$ 1.234,56").
     */
    public function toReais(): string
    {
        $sign = $this->cents < 0 ? '-' : '';
        $absolute = abs($this->cents);
        $reais = intdiv($absolute, 100);
        $fraction = $absolute % 100;

        $grouped = number_format($reais, 0, ',', '.');

        return sprintf('%sR$ %s,%02d', $sign, $grouped, $fraction);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }
}
