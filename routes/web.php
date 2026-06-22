<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * Stockr is a pure REST API. The only web entry point is a small JSON landing
 * response pointing clients at the versioned API and its OpenAPI docs.
 */
Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'api' => url('/api/v1'),
    'docs' => url('/docs/api'),
]));
