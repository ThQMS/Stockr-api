<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Auth;

use Illuminate\Contracts\Hashing\Hasher;
use Stockr\Domain\Auth\Contracts\CredentialVerifierInterface;
use Stockr\Domain\Auth\ValueObjects\Email;
use Stockr\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Verifies a plaintext password against the stored hash. Lives in Infrastructure
 * because it reads the persisted credential the Domain entity never exposes.
 */
final readonly class EloquentCredentialVerifier implements CredentialVerifierInterface
{
    public function __construct(private Hasher $hasher) {}

    public function verify(Email $email, string $plainPassword): bool
    {
        $user = UserModel::query()->where('email', (string) $email)->first();

        if ($user === null) {
            // Hash a dummy value to keep timing roughly constant.
            $this->hasher->check($plainPassword, '$2y$12$'.str_repeat('.', 53));

            return false;
        }

        return $this->hasher->check($plainPassword, $user->password);
    }
}
