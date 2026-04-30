<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationAutoReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $name,
        protected ?string $venueName = null,
        protected ?string $reservationDateTime = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【自動返信】ご予約ありがとうございます',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reservation-auto-reply',
            text: 'emails.reservation-auto-reply-text',
            with: [
                'name' => $this->name,
                'venueName' => $this->venueName,
                'reservationDateTime' => $this->reservationDateTime,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
