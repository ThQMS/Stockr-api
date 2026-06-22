<?php

declare(strict_types=1);

namespace Stockr\Application\Auth\UseCases;

use RuntimeException;
use Stockr\Application\Auth\DTOs\AuthResultDTO;
use Stockr\Application\Auth\DTOs\RegisterUserDTO;
use Stockr\Domain\Auth\Contracts\PasswordHasherInterface;
use Stockr\Domain\Auth\Contracts\TokenIssuerInterface;
use Stockr\Domain\Auth\Entities\User;
use Stockr\Domain\Auth\Entities\Workspace;
use Stockr\Domain\Auth\Repositories\UserRepositoryInterface;
use Stockr\Domain\Auth\Repositories\WorkspaceRepositoryInterface;
use Stockr\Domain\Auth\ValueObjects\Email;
use Stockr\Domain\Auth\ValueObjects\WorkspaceSlug;

/**
 * Registers a new account, provisions its first workspace, and issues an API token.
 */
final readonly class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private WorkspaceRepositoryInterface $workspaces,
        private PasswordHasherInterface $hasher,
        private TokenIssuerInterface $tokens,
    ) {}

    public function execute(RegisterUserDTO $dto): AuthResultDTO
    {
        $email = new Email($dto->email);

        if ($this->users->findByEmail($email) !== null) {
            throw new RuntimeException(sprintf('Email "%s" is already registered.', $email));
        }

        $user = $this->users->create(
            new User(id: null, name: $dto->name, email: $email),
            $this->hasher->hash($dto->password),
        );

        $userId = (int) $user->id;

        $workspace = $this->workspaces->save(new Workspace(
            id: null,
            name: $dto->workspaceName,
            slug: WorkspaceSlug::fromName($dto->workspaceName),
            ownerId: $userId,
        ));

        $token = $this->tokens->issue($userId);

        return new AuthResultDTO(
            userId: $userId,
            name: $user->name(),
            email: (string) $user->email(),
            token: $token,
            workspaceIds: [(int) $workspace->id],
        );
    }
}
