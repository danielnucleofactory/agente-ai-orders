<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PorthUnmatchedPortAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $context
    ) {
    }

    public function envelope(): Envelope
    {
        $orderNumber = $this->context['order_number'] ?? 'N/A';
        $portRole = $this->context['port_role_label'] ?? 'Puerto';

        return new Envelope(
            subject: "Alerta Porth: puerto sin match ({$portRole}) - PO {$orderNumber}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.porth-unmatched-port-alert',
            with: [
                'context' => $this->context,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
