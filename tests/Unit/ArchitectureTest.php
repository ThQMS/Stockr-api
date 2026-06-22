<?php

declare(strict_types=1);

/*
 * Architectural guardrails for the DDD layering. These encode the four rules
 * from the project brief as executable tests.
 */

arch('Domain never depends on the framework')
    ->expect('Stockr\Domain')
    ->not->toUse('Illuminate');

arch('Application never depends on Eloquent')
    ->expect('Stockr\Application')
    ->not->toUse('Illuminate\Database\Eloquent');

arch('Application never references the Infrastructure layer')
    ->expect('Stockr\Application')
    ->not->toUse('Stockr\Infrastructure');

arch('Domain never references outer layers')
    ->expect('Stockr\Domain')
    ->not->toUse([
        'Stockr\Application',
        'Stockr\Infrastructure',
        'Stockr\Presentation',
    ]);
