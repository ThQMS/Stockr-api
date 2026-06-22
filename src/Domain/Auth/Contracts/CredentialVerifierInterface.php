<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Contracts;

use Stockr\Domain\Auth\ValueObjects\Email;

/**
 * Port for resolving and verifying a user's stored credentials. Implemented in
 * Infrastructure (it needs access to the persisted password hash, which the
 * Domain User entity deliberately does not carry).
 */
interface CredentialVerifierInterface
{
    public function verify(Email $email, string $plainPassword): bool;
}
