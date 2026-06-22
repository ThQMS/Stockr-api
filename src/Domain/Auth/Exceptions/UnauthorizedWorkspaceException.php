<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Exceptions;

use RuntimeException;

final class UnauthorizedWorkspaceException extends RuntimeException
{
    public function __construct(string $message = 'This resource does not belong to the active workspace.')
    {
        parent::__construct($message);
    }

    public static function forUser(int $userId, int $workspaceId): self
    {
        return new self(sprintf('User %d is not a member of workspace %d.', $userId, $workspaceId));
    }
}
