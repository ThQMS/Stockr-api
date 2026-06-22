<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Auth;

use RuntimeException;
use Stockr\Domain\Auth\Contracts\TokenIssuerInterface;
use Stockr\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Issues Sanctum personal access tokens for the Domain's token port.
 */
final class SanctumTokenIssuer implements TokenIssuerInterface
{
    public function issue(int $userId, string $name = 'api', array $abilities = ['*']): string
    {
        $user = UserModel::query()->findOrFail($userId);

        return $user->createToken($name, $abilities)->plainTextToken;
    }

    public function revokeAll(int $userId): void
    {
        $user = UserModel::query()->find($userId);

        if ($user === null) {
            throw new RuntimeException(sprintf('User %d not found.', $userId));
        }

        $user->tokens()->delete();
    }
}
