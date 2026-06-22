<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Stockr\Application\Inventory\DTOs\CreateProductDTO;
use Stockr\Application\Inventory\UseCases\CreateProductUseCase;
use Stockr\Application\Inventory\UseCases\GenerateProductQrCodeUseCase;
use Stockr\Application\Inventory\UseCases\ScanProductUseCase;
use Stockr\Application\Inventory\UseCases\UpdateProductUseCase;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Stockr\Presentation\Http\Requests\ScanProductRequest;
use Stockr\Presentation\Http\Requests\StoreProductRequest;
use Stockr\Presentation\Http\Requests\UpdateProductRequest;
use Stockr\Presentation\Http\Resources\ProductResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * Product catalogue endpoints. Reads use spatie/laravel-query-builder against the
 * Eloquent model (a thin read model); writes are delegated to Application use cases.
 */
#[Prefix('api/v1/products')]
#[Middleware(['auth:sanctum', 'workspace'])]
final class ProductController
{
    #[Get('/', name: 'products.index')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        $products = QueryBuilder::for(ProductModel::query()->where('workspace_id', $workspaceId))
            ->allowedFilters([
                'name',
                'sku',
                AllowedFilter::exact('category_id'),
                AllowedFilter::scope('low_stock'),
            ])
            ->allowedSorts(['name', 'sku', 'current_stock', 'cost_price'])
            ->defaultSort('name')
            ->paginate((int) $request->integer('per_page', 15))
            ->appends($request->query());

        return ProductResource::collection($products);
    }

    #[Post('/', name: 'products.store')]
    public function store(StoreProductRequest $request, CreateProductUseCase $useCase): JsonResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        $product = $useCase->execute(CreateProductDTO::from(
            workspaceId: $workspaceId,
            name: $request->string('name')->toString(),
            costPrice: (float) $request->input('cost_price'),
            sku: $request->filled('sku') ? $request->string('sku')->toString() : null,
            initialStock: $request->integer('initial_stock', 0),
            minimumStock: $request->integer('minimum_stock', 0),
            categoryId: $request->input('category_id') !== null ? $request->integer('category_id') : null,
            description: $request->input('description'),
            salePrice: (float) $request->input('sale_price', 0),
            barcode: $request->filled('barcode') ? $request->string('barcode')->toString() : null,
            unit: $request->string('unit', 'un')->toString(),
        ));

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[Get('{product}', name: 'products.show')]
    public function show(Request $request, string $product, ProductRepositoryInterface $products): ProductResource
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');
        $entity = $products->findById($product);

        // Cross-workspace access is indistinguishable from "not found".
        abort_if($entity === null || $entity->getWorkspaceId() !== $workspaceId, Response::HTTP_NOT_FOUND, 'Product not found.');

        return new ProductResource($entity);
    }

    #[Put('{product}', name: 'products.update')]
    public function update(UpdateProductRequest $request, string $product, UpdateProductUseCase $useCase): ProductResource
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        $updated = $useCase->execute(
            workspaceId: $workspaceId,
            productId: $product,
            name: $request->input('name'),
            price: $request->has('cost_price') ? (float) $request->input('cost_price') : null,
            reorderLevel: $request->has('minimum_stock') ? $request->integer('minimum_stock') : null,
            description: $request->input('description'),
        );

        return new ProductResource($updated);
    }

    #[Delete('{product}', name: 'products.destroy')]
    public function destroy(Request $request, string $product, ProductRepositoryInterface $products): JsonResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');
        $entity = $products->findById($product);

        abort_if($entity === null || $entity->getWorkspaceId() !== $workspaceId, Response::HTTP_NOT_FOUND, 'Product not found.');

        $products->delete($entity);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Get('{product}/qrcode', name: 'products.qrcode')]
    public function qrCode(Request $request, string $product, GenerateProductQrCodeUseCase $useCase): JsonResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        $result = $useCase->execute($workspaceId, $product, $request->integer('size', 300));

        return response()->json($result);
    }

    #[Post('scan', name: 'products.scan')]
    public function scan(ScanProductRequest $request, ScanProductUseCase $useCase): JsonResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        $result = $useCase->execute($workspaceId, $request->string('code')->toString());

        return response()->json($result);
    }
}
