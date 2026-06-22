<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * A validated, normalised (lower-cased) email address.
 */
final class Email implements Stringable
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid email address.', $value));
        }

        $this->value = $normalized;
    }

    public function domain(): string
    {
        return substr($this->value, (int) strpos($this->value, '@') + 1);
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
