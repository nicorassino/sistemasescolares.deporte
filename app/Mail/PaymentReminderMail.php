<?php

namespace App\Mail;

use App\Models\Fee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Fee $fee
    ) {
        $this->fee->load(['student']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aviso de vencimiento de cuota - Juvenilia',
            replyTo: [
                new Address('juvefutbol@institutojuvenilia.edu.ar', 'Escuela de Fútbol Instituto Juvenilia'),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
            text: 'emails.payment-reminder-text',
        );
    }
}
