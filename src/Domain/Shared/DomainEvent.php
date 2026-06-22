<?php

declare(strict_types=1);

namespace Stockr\Domain\Shared;

use DateTimeImmutable;

/**
 * Marker interface for domain events. Every event records the instant it
 * happened so listeners and audit trails have a consistent timestamp.
 */
interface DomainEvent
{
    public function occurredOn(): DateTimeImmutable;
}
