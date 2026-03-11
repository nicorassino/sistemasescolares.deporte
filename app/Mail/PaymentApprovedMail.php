<?php

namespace App\Mail;

use App\Models\Fee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PaymentApprovedMail extends Mailable
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
            subject: 'Recibo de pago acreditado - Juvenilia',
        );
    }

    public function content(): Content
    {
        $receiptUrl = URL::temporarySignedRoute(
            'receipt.download.signed',
            now()->addDays(7),
            ['fee' => $this->fee]
        );

        return new Content(
            view: 'emails.payment-approved',
            with: ['receiptUrl' => $receiptUrl],
        );
    }
}
