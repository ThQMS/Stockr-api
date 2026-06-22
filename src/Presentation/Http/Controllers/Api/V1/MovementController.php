<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Stockr\Application\Inventory\DTOs\RegisterMovementDTO;
use Stockr\Application\Inventory\UseCases\RegisterMovementUseCase;
use Stockr\Domain\Inventory\Repositories\MovementRepositoryInterface;
use Stockr\Presentation\Http\Requests\RegisterMovementRequest;
use Stockr\Presentation\Http\Resources\MovementResource;

/**
 * Stock movement endpoints, nested under a product.
 */
#[Prefix('api/v1/products/{product}/movements')]
#[Middleware(['auth:sanctum', 'workspace'])]
final class MovementController
{
    #[Get('/', name: 'movements.index')]
    public function index(Request $request, string $product, MovementRepositoryInterface $movements): AnonymousResourceCollection
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        return MovementResource::collection($movements->forProduct($product, $workspaceId));
    }

    #[Post('/', name: 'movements.store')]
    public function store(RegisterMovementRequest $request, string $product, RegisterMovementUseCase $useCase): JsonResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        $result = $useCase->execute(RegisterMovementDTO::from(
            productId: $product,
            workspaceId: $workspaceId,
            userId: (int) $request->user()?->getAuthIdentifier(),
            type: $request->string('type')->toString(),
            quantity: $request->integer('quantity'),
            notes: $request->input('notes'),
            referenceCode: $request->input('reference_code'),
        ));

        return response()->json($result, JsonResponse::HTTP_CREATED);
    }
}
