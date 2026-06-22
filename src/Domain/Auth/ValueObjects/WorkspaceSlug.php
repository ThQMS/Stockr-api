<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * URL-safe workspace identifier: lowercase letters, digits and hyphens, 3–50
 * chars. Built from a display name via slugify, optionally with a short random
 * suffix to resolve collisions (the uniqueness check itself belongs to the
 * persistence layer).
 */
final class WorkspaceSlug implements Stringable
{
    private const PATTERN = '/^[a-z0-9-]{3,50}$/';

    private function __construct(
        private readonly string $value,
    ) {
        if (preg_match(self::PATTERN, $value) !== 1) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid workspace slug.', $value));
        }
    }

    /**
     * Hydrate from an existing slug string (e.g. read from persistence).
     */
    public static function fromString(string $value): self
    {
        return new self(strtolower(trim($value)));
    }

    public static function fromName(string $name): self
    {
        return new self(self::slugify($name));
    }

    /**
     * Slugify the name and append a random 4-character suffix. Use this to
     * retry when {@see fromName} produced a slug that already exists.
     */
    public static function fromNameWithRandomSuffix(string $name): self
    {
        $base = self::slugify($name);
        $suffix = substr(bin2hex(random_bytes(2)), 0, 4);

        // Keep within the 50-char ceiling once the "-xxxx" suffix is appended.
        $base = substr($base, 0, 45);

        return new self(rtrim($base, '-').'-'.$suffix);
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

    private static function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }
}
