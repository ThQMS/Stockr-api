<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Auth;

use Illuminate\Contracts\Hashing\Hasher;
use Stockr\Domain\Auth\Contracts\PasswordHasherInterface;

final readonly class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private Hasher $hasher) {}

    public function hash(string $plain): string
    {
        return $this->hasher->make($plain);
    }

    public function verify(string $plain, string $hashed): bool
    {
        return $this->hasher->check($plain, $hashed);
    }
}
