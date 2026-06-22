<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Stockr\Application\Auth\DTOs\RegisterUserDTO;
use Stockr\Application\Auth\UseCases\AuthenticateUserUseCase;
use Stockr\Application\Auth\UseCases\RegisterUserUseCase;
use Stockr\Application\Auth\UseCases\SelectWorkspaceUseCase;
use Stockr\Domain\Auth\Contracts\TokenIssuerInterface;
use Stockr\Presentation\Http\Requests\LoginRequest;
use Stockr\Presentation\Http\Requests\RegisterRequest;
use Stockr\Presentation\Http\Requests\SelectWorkspaceRequest;
use Stockr\Presentation\Http\Resources\WorkspaceResource;

/**
 * Authentication endpoints. Pure orchestration: every branch delegates to an
 * Application use case and shapes the HTTP response — no business rules here.
 */
#[Prefix('api/v1/auth')]
final class AuthController
{
    #[Post('register', name: 'auth.register')]
    public function register(RegisterRequest $request, RegisterUserUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(RegisterUserDTO::from([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'workspaceName' => $request->string('workspace_name')->toString(),
        ]));

        return response()->json($result, JsonResponse::HTTP_CREATED);
    }

    #[Post('login', name: 'auth.login')]
    public function login(LoginRequest $request, AuthenticateUserUseCase $useCase): JsonResponse
    {
        $result = $useCase->execute(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json($result);
    }

    #[Get('me', name: 'auth.me', middleware: 'auth:sanctum')]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user?->getAuthIdentifier(),
            'name' => $user?->getAttribute('name'),
            'email' => $user?->getAttribute('email'),
        ]);
    }

    #[Post('workspace', name: 'auth.workspace.select', middleware: 'auth:sanctum')]
    public function selectWorkspace(SelectWorkspaceRequest $request, SelectWorkspaceUseCase $useCase): WorkspaceResource
    {
        $workspace = $useCase->execute(
            (int) $request->user()?->getAuthIdentifier(),
            $request->integer('workspace_id'),
        );

        return new WorkspaceResource($workspace);
    }

    #[Post('logout', name: 'auth.logout', middleware: 'auth:sanctum')]
    public function logout(Request $request, TokenIssuerInterface $tokens): JsonResponse
    {
        $tokens->revokeAll((int) $request->user()?->getAuthIdentifier());

        return response()->json(['message' => 'Logged out.']);
    }
}
