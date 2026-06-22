<?php

declare(strict_types=1);

namespace Stockr\Domain\Shared;

/**
 * Port used by Application use cases to publish domain events without coupling
 * to any concrete event bus. Implemented in Infrastructure.
 */
interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
