<?php

declare(strict_types=1);

namespace Stockr\Application\Auth\UseCases;

use RuntimeException;
use Stockr\Application\Auth\DTOs\AuthResultDTO;
use Stockr\Domain\Auth\Contracts\CredentialVerifierInterface;
use Stockr\Domain\Auth\Contracts\TokenIssuerInterface;
use Stockr\Domain\Auth\Repositories\UserRepositoryInterface;
use Stockr\Domain\Auth\ValueObjects\Email;

/**
 * Verifies credentials and, on success, issues a fresh API token.
 */
final readonly class AuthenticateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $users,
        private CredentialVerifierInterface $credentials,
        private TokenIssuerInterface $tokens,
    ) {}

    public function execute(string $emailInput, string $password): AuthResultDTO
    {
        $email = new Email($emailInput);

        if (! $this->credentials->verify($email, $password)) {
            throw new RuntimeException('Invalid credentials.');
        }

        $user = $this->users->findByEmail($email);

        if ($user === null) {
            throw new RuntimeException('Invalid credentials.');
        }

        $userId = (int) $user->id;

        return new AuthResultDTO(
            userId: $userId,
            name: $user->name(),
            email: (string) $user->email(),
            token: $this->tokens->issue($userId),
            workspaceIds: $user->workspaceIds(),
        );
    }
}
