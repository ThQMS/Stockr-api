<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Contracts;

/**
 * Port for issuing and revoking API access tokens. Implemented in Infrastructure
 * over Laravel Sanctum.
 */
interface TokenIssuerInterface
{
    /**
     * @param  list<string>  $abilities
     */
    public function issue(int $userId, string $name = 'api', array $abilities = ['*']): string;

    public function revokeAll(int $userId): void;
}
