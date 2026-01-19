<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $userEmail;
    public $subject;
    public $description;
    public $createdAt;
    public $ccEmails;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $userEmail, $subject, $description, $ccEmails = [])
    {
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->subject = $subject;
        $this->description = $description;
        $this->createdAt = now()->format('d/m/Y H:i');
        $this->ccEmails = is_array($ccEmails) ? $ccEmails : (empty($ccEmails) ? [] : [$ccEmails]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Preparar direcciones CC si existen
        $ccAddresses = [];
        if (!empty($this->ccEmails)) {
            foreach ($this->ccEmails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $ccAddresses[] = new Address($email);
                }
            }
        }

        return new Envelope(
            subject: 'Nueva Solicitud de Soporte: ' . $this->subject,
            replyTo: [
                new Address($this->userEmail, $this->userName)
            ],
            cc: !empty($ccAddresses) ? $ccAddresses : null,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.support-request-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}



