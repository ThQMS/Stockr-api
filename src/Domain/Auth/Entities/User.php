<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Entities;

use Stockr\Domain\Auth\ValueObjects\Email;

/**
 * Domain representation of an account. Holds no password hashing or persistence
 * logic — that belongs to Infrastructure. Membership is tracked as workspace ids.
 */
final class User
{
    /**
     * @param  list<int>  $workspaceIds
     */
    public function __construct(
        public readonly ?int $id,
        private string $name,
        private Email $email,
        private array $workspaceIds = [],
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    /**
     * @return list<int>
     */
    public function workspaceIds(): array
    {
        return $this->workspaceIds;
    }

    public function belongsToWorkspace(int $workspaceId): bool
    {
        return in_array($workspaceId, $this->workspaceIds, true);
    }
}
