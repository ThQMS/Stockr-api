<?php

declare(strict_types=1);

namespace Stockr\Application\Auth\DTOs;

use Spatie\LaravelData\Data;

/**
 * Result of an authentication or registration flow: the issued token plus the
 * identity it belongs to.
 */
final class AuthResultDTO extends Data
{
    /**
     * @param  list<int>  $workspaceIds
     */
    public function __construct(
        public int $userId,
        public string $name,
        public string $email,
        public string $token,
        public array $workspaceIds = [],
    ) {}
}
