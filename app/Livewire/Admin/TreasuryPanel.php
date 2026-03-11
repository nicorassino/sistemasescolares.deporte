<?php

namespace App\Livewire\Admin;

use App\Mail\PaymentApprovedMail;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TreasuryPanel extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        $payments = Payment::query()
            ->where('status', 'pending_review')
            ->with(['fee.student', 'tutor'])
            ->orderBy('created_at')
            ->get();

        return view('livewire.admin.treasury-panel', [
            'payments' => $payments,
        ]);
    }

    public function approvePayment(int $paymentId): void
    {
        $payment = Payment::where('status', 'pending_review')
            ->with(['fee.student', 'tutor.user'])
            ->findOrFail($paymentId);

        $payment->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $payment->fee->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $tutorEmail = $payment->tutor->user?->email;
        if ($tutorEmail) {
            Mail::to($tutorEmail)->send(new PaymentApprovedMail($payment->fee));
        }

        session()->flash('status', 'Pago aprobado correctamente.');
    }

    public function rejectPayment(int $paymentId): void
    {
        $payment = Payment::where('status', 'pending_review')
            ->with('fee')
            ->findOrFail($paymentId);

        if ($payment->evidence_file_path && Storage::exists($payment->evidence_file_path)) {
            Storage::delete($payment->evidence_file_path);
        }

        $payment->update([
            'status' => 'rejected',
            'evidence_file_path' => null,
            'evidence_file_size' => null,
            'evidence_mime_type' => null,
            'transfer_sender_name' => null,
        ]);

        $payment->fee->update([
            'status' => 'pending',
            'paid_at' => null,
        ]);

        session()->flash('status', 'Pago rechazado y comprobante eliminado.');
    }
}
