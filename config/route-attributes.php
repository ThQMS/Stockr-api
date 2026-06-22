<?php

use Illuminate\Routing\Middleware\SubstituteBindings;

return [
    /*
     *  Automatic registration of routes will only happen if this setting is `true`
     */
    'enabled' => true,

    /*
     * Controllers in these directories that have routing attributes
     * will automatically be registered.
     *
     * Optionally, you can specify group configuration by using key/values
     */
    'directories' => [
        // DDD: controllers live in the Presentation layer, not app/. We must tell
        // the registrar the namespace + base_path so it can resolve FQCNs from
        // files outside app/. Full "api/v1/..." prefixes are declared per
        // controller via #[Prefix].
        base_path('src/Presentation/Http/Controllers') => [
            'namespace' => 'Stockr\\Presentation\\Http\\Controllers',
            'base_path' => base_path('src/Presentation/Http/Controllers'),
            'middleware' => 'api',
            'patterns' => ['*Controller.php'],
            'not_patterns' => [],
        ],
    ],

    /*
     * This middleware will be applied to all routes.
     */
    'middleware' => [
        SubstituteBindings::class,
    ],

    /*
     * When enabled, implicitly scoped bindings will be enabled by default.
     * You can override this behaviour by using the `ScopeBindings` attribute, and passing `false` to it.
     *
     * Possible values:
     *  - null: use the default behaviour
     *  - true: enable implicitly scoped bindings for all routes
     *  - false: disable implicitly scoped bindings for all routes
     */
    'scope-bindings' => null,
];
