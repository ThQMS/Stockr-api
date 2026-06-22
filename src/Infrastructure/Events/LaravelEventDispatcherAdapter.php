<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Stockr\Domain\Shared\EventDispatcherInterface;

/**
 * Bridges the Domain's event port onto Laravel's event dispatcher.
 */
final readonly class LaravelEventDispatcherAdapter implements EventDispatcherInterface
{
    public function __construct(private Dispatcher $dispatcher) {}

    public function dispatch(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
