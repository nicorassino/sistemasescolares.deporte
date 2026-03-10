<?php

namespace App\Livewire\Tutor;

use App\Models\Fee;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class TutorDashboard extends Component
{
    use WithFileUploads;

    public ?int $selectedFeeId = null;

    public $paymentProof;

    public bool $showPaymentModal = false;

    #[Layout('layouts.tutor')]
    public function render()
    {
        $user = Auth::user();

        $tutor = $user?->tutor()
            ->with([
                'students.fees' => function ($query) {
                    $query->where('status', 'pending')
                        ->orderBy('due_date');
                },
            ])
            ->first();

        return view('livewire.tutor.tutor-dashboard', [
            'tutor' => $tutor,
        ]);
    }

    public function openPaymentModal(int $feeId): void
    {
        $this->selectedFeeId = $feeId;
        $this->paymentProof = null;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentProof = null;
        $this->selectedFeeId = null;
    }

    public function submitPaymentProof(): void
    {
        $this->validate([
            'paymentProof' => ['required', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:2048'],
        ], [
            'paymentProof.required' => 'Debe adjuntar el comprobante.',
            'paymentProof.mimes' => 'El archivo debe ser una imagen (jpg, jpeg, png) o PDF.',
            'paymentProof.max' => 'El archivo no puede superar los 2MB.',
        ]);

        $fee = Fee::where('id', $this->selectedFeeId)
            ->where('status', 'pending')
            ->firstOrFail();

        $user = Auth::user();
        $tutor = $user->tutor;

        $path = $this->paymentProof->store('payments');

        Payment::create([
            'fee_id' => $fee->id,
            'tutor_id' => $tutor->id,
            'amount_reported' => $fee->amount,
            'paid_on_date' => now()->toDateString(),
            'status' => 'pending_review',
            'evidence_file_path' => $path,
            'evidence_file_size' => Storage::disk(config('filesystems.default'))->size($path),
            'evidence_mime_type' => $this->paymentProof->getClientMimeType(),
        ]);

        $fee->status = 'pending';
        $fee->save();

        $this->closePaymentModal();

        session()->flash('status', 'Comprobante enviado. La administración validará su pago a la brevedad');
    }
}

