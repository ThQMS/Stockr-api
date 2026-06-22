<?php

declare(strict_types=1);

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
     * Only routes under this prefix are documented. Stockr exposes a single
     * versioned API surface at /api/v1.
     */
    'api_path' => 'api/v1',

    /*
     * The domain the API is served from. Null uses the current request host.
     */
    'api_domain' => null,

    /*
     * Where the generated OpenAPI document is written when exported.
     */
    'export_path' => 'api.json',

    'info' => [
        // Scramble reads the document title from `ui.title` (below); version and
        // description come from here.
        'title' => 'Stockr API',
        'version' => '1.0.0',
        'description' => 'REST API for multi-workspace inventory management: products, '
            .'stock movements (immutable ledger), QR code scanning and inventory reports. '
            .'Authenticate with a Bearer token (Sanctum) and select the active workspace '
            .'via the X-Workspace-Id header.',
    ],

    'ui' => [
        'title' => 'Stockr API',
    ],

    /*
     * Customize the generated OpenAPI document at runtime if needed.
     */
    'servers' => null,

    'enum_cases_description_strategy' => 'description',

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],
];
