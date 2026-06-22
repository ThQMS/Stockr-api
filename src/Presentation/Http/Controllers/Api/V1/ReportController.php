<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Stockr\Application\Inventory\UseCases\GetInventoryReportUseCase;
use Stockr\Domain\Inventory\Repositories\ProductRepositoryInterface;
use Stockr\Presentation\Http\Resources\ProductResource;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Inventory reporting endpoints. Each method only orchestrates a use case /
 * repository and shapes the response — no business logic.
 */
#[Prefix('api/v1/reports')]
#[Middleware(['auth:sanctum', 'workspace'])]
final class ReportController
{
    #[Get('summary', name: 'reports.summary')]
    public function summary(Request $request, GetInventoryReportUseCase $useCase): JsonResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        return response()->json($useCase->execute($workspaceId));
    }

    #[Get('chart', name: 'reports.chart')]
    public function chart(Request $request, GetInventoryReportUseCase $useCase): JsonResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');
        $report = $useCase->execute($workspaceId);

        // Reshape the report lines into a chart-friendly series (top by value).
        $points = array_map(
            static fn ($line): array => [
                'label' => $line->sku,
                'stock' => $line->stock,
                'value' => $line->lineValue,
            ],
            $report->lines,
        );

        usort($points, static fn (array $a, array $b): int => $b['value'] <=> $a['value']);

        return response()->json([
            'workspace_id' => $report->workspaceId,
            'total_stock_value' => $report->totalStockValue,
            'series' => array_slice($points, 0, 20),
        ]);
    }

    #[Get('low-stock', name: 'reports.low-stock')]
    public function lowStock(Request $request, ProductRepositoryInterface $products): AnonymousResourceCollection
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');

        return ProductResource::collection($products->lowStock($workspaceId)->toArray());
    }

    #[Get('export', name: 'reports.export')]
    public function export(Request $request, GetInventoryReportUseCase $useCase): StreamedResponse
    {
        $workspaceId = (int) $request->attributes->get('workspaceId');
        $report = $useCase->execute($workspaceId);

        return response()->streamDownload(function () use ($report): void {
            $out = fopen('php://output', 'wb');

            if ($out === false) {
                return;
            }

            fputcsv($out, ['sku', 'name', 'stock', 'minimum_stock', 'unit_price', 'line_value', 'low_stock']);

            foreach ($report->lines as $line) {
                fputcsv($out, [
                    $line->sku,
                    $line->name,
                    $line->stock,
                    $line->reorderLevel,
                    number_format($line->unitPrice, 2, '.', ''),
                    number_format($line->lineValue, 2, '.', ''),
                    $line->isLowStock ? '1' : '0',
                ]);
            }

            fclose($out);
        }, "inventory-{$workspaceId}.csv", ['Content-Type' => 'text/csv']);
    }
}
