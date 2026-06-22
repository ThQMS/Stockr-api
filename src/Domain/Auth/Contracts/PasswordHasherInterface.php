<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Contracts;

/**
 * Port for password hashing/verification. Implemented in Infrastructure over
 * Laravel's Hash facade so the Application layer never touches the framework.
 */
interface PasswordHasherInterface
{
    public function hash(string $plain): string;

    public function verify(string $plain, string $hashed): bool;
}
