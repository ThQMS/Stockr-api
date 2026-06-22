<?php

declare(strict_types=1);

namespace Stockr\Domain\Auth\Repositories;

use Stockr\Domain\Auth\Entities\User;
use Stockr\Domain\Auth\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(Email $email): ?User;

    /**
     * Persist a user together with its (already hashed) credentials.
     */
    public function create(User $user, string $hashedPassword): User;
}
