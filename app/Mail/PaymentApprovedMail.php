<?php

namespace App\Mail;

use App\Models\Fee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PaymentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Fee $fee,
        public ?float $appliedAmount = null,
        public ?float $remainingAmount = null
    ) {
        $this->fee->load(['student']);
    }

    public function envelope(): Envelope
    {
        $isPartial = ($this->remainingAmount ?? 0) > 0;

        return new Envelope(
            subject: $isPartial
                ? 'Pago parcial acreditado - Juvenilia'
                : 'Recibo de pago acreditado - Juvenilia',
            replyTo: [
                new Address('juvefutbol@institutojuvenilia.edu.ar', 'Escuela de Fútbol Instituto Juvenilia'),
            ],
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
            text: 'emails.payment-approved-text',
            with: [
                'receiptUrl' => $receiptUrl,
                'appliedAmount' => $this->appliedAmount ?? (float) $this->fee->amount,
                'remainingAmount' => $this->remainingAmount ?? 0,
                'isPartial' => ($this->remainingAmount ?? 0) > 0,
            ],
        );
    }
}
