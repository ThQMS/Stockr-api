<?php

declare(strict_types=1);

namespace Stockr\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Stockr\Domain\Inventory\Events\LowStockDetected;

/**
 * Notifies a workspace that a product reached its reorder level. Built from the
 * LowStockDetected domain event so no Eloquent model leaks into the message.
 */
final class LowStockNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly LowStockDetected $event) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Estoque baixo: %s', (string) $this->event->product->sku()),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.low-stock',
            with: [
                'sku' => (string) $this->event->product->sku(),
                'currentStock' => $this->event->product->stock()->getValue(),
                'reorderLevel' => $this->event->threshold->getValue(),
            ],
        );
    }
}
