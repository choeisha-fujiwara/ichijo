<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationPostedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected array $payload,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【予約通知】予約フォームから新しい投稿がありました',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reservation-posted-notification',
            text: 'emails.reservation-posted-notification-text',
            with: [
                'payload' => $this->payload,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
