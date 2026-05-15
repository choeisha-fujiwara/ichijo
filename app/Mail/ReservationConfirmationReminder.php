<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmationReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected Reservation $reservation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【予約確認】明日のご来場をお待ちしています',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.reservation-confirmation-reminder',
            text: 'emails.reservation-confirmation-reminder-text',
            with: [
                'reservation' => $this->reservation,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
