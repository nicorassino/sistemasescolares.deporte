<?php

namespace App\Livewire\Tutor;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class TutorDashboard extends Component
{
    use WithFileUploads;

    public ?int $selectedFeeId = null;

    public string $transfer_sender_name = '';

    public $paymentProof;

    public bool $showPaymentModal = false;

    /** Sección visible: escuela | novedades | cuotas */
    public string $activeSection = 'escuela';

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
            'announcements' => Announcement::orderByDesc('created_at')->limit(15)->get(),
        ]);
    }

    public function openPaymentModal(int $feeId): void
    {
        $this->selectedFeeId = $feeId;
        $this->transfer_sender_name = '';
        $this->paymentProof = null;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentProof = null;
        $this->transfer_sender_name = '';
        $this->selectedFeeId = null;
    }

    public function submitPaymentProof(): void
    {
        $this->validate([
            'transfer_sender_name' => ['required', 'string', 'max:255'],
            'paymentProof' => ['required', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:2048'],
        ], [
            'transfer_sender_name.required' => 'El nombre del titular de la cuenta origen es obligatorio.',
            'transfer_sender_name.max' => 'El nombre no puede superar los 255 caracteres.',
            'paymentProof.required' => 'Debe adjuntar el comprobante.',
            'paymentProof.mimes' => 'El archivo debe ser una imagen (jpg, jpeg, png) o PDF.',
            'paymentProof.max' => 'El archivo no puede superar los 2MB.',
        ]);

        $fee = Fee::with('student')
            ->where('id', $this->selectedFeeId)
            ->where('status', 'pending')
            ->firstOrFail();

        $user = Auth::user();
        $tutor = $user->tutor;

        $student = $fee->student;
        $dni = $student?->dni ?: 'sin-dni';
        $timestamp = now()->format('Ymd_His');
        $extension = $this->paymentProof->getClientOriginalExtension() ?: 'pdf';
        $safeDni = preg_replace('/[^0-9A-Za-z\-]/', '_', $dni);
        $fileName = "{$safeDni}_fee{$fee->id}_{$timestamp}.{$extension}";

        $path = $this->paymentProof->storeAs('payments', $fileName);

        Payment::create([
            'fee_id' => $fee->id,
            'tutor_id' => $tutor->id,
            'amount_reported' => $fee->amount,
            'paid_on_date' => now()->toDateString(),
            'status' => 'pending_review',
            'evidence_file_path' => $path,
            'evidence_file_size' => Storage::disk(config('filesystems.default'))->size($path),
            'evidence_mime_type' => $this->paymentProof->getClientMimeType(),
            'transfer_sender_name' => trim($this->transfer_sender_name),
        ]);

        $fee->status = 'pending';
        $fee->save();

        $this->closePaymentModal();

        session()->flash('status', 'Comprobante enviado. La administración validará su pago a la brevedad');
    }
}

