<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Stockr\Domain\Auth\Exceptions\UnauthorizedWorkspaceException;
use Stockr\Domain\Inventory\Exceptions\DuplicateSkuException;
use Stockr\Domain\Inventory\Exceptions\ProductNotFoundException;
use Stockr\Presentation\Http\Middleware\EnsureWorkspaceMember;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // API routes are registered via spatie/laravel-route-attributes
        // (see config/route-attributes.php). routes/api.php is kept empty.
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'workspace' => EnsureWorkspaceMember::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Map domain failures to API-friendly JSON responses.
        $exceptions->render(fn (UnauthorizedWorkspaceException $e) => response()->json(['message' => $e->getMessage()], 403));
        $exceptions->render(fn (ProductNotFoundException $e) => response()->json(['message' => $e->getMessage()], 404));
        $exceptions->render(fn (DuplicateSkuException $e) => response()->json(['message' => $e->getMessage()], 409));
    })->create();
