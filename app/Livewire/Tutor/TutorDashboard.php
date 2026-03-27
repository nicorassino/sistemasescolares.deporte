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
    public string $transfer_amount = '';

    public $paymentProof;

    public bool $showPaymentModal = false;

    /** Sección visible: escuela | novedades | cuotas */
    public string $activeSection = 'escuela';

    /** Modal de cuotas pagadas */
    public bool $showPaidModal = false;
    public ?int $paidStudentId = null;
    public ?int $paidFilterYear = null;

    #[Layout('layouts.tutor')]
    public function render()
    {
        $user = Auth::user();
        $tutorId = $user?->tutor?->id;

        $tutor = $user?->tutor()
            ->with([
                'students.fees' => function ($query) use ($tutorId) {
                    $query->orderBy('due_date');
                    $query->with([
                        'payments' => function ($paymentQuery) use ($tutorId) {
                            $paymentQuery
                                ->where('tutor_id', $tutorId)
                                ->orderByDesc('id');
                        },
                    ]);
                },
            ])
            ->first();

        return view('livewire.tutor.tutor-dashboard', [
            'tutor' => $tutor,
            'announcements' => Announcement::orderByDesc('created_at')->limit(15)->get(),
        ]);
    }

    public function openPaidModal(int $studentId): void
    {
        $this->paidStudentId = $studentId;
        $this->paidFilterYear = null;
        $this->showPaidModal = true;
    }

    public function closePaidModal(): void
    {
        $this->showPaidModal = false;
        $this->paidStudentId = null;
        $this->paidFilterYear = null;
    }

    public function openPaymentModal(int $feeId): void
    {
        $user = Auth::user();
        $tutor = $user?->tutor;

        $fee = Fee::query()
            ->where('id', $feeId)
            ->whereIn('status', ['pending', 'partial'])
            ->firstOrFail();

        $this->selectedFeeId = $fee->id;
        $this->transfer_sender_name = '';
        $this->paymentProof = null;
        $remaining = max((float) $fee->amount - (float) $fee->paid_amount, 0);
        $this->transfer_amount = number_format($remaining, 2, '.', '');

        if ($tutor) {
            $existingPayment = Payment::query()
                ->where('fee_id', $feeId)
                ->where('tutor_id', $tutor->id)
                ->latest('id')
                ->first();

            if ($existingPayment?->transfer_sender_name) {
                $this->transfer_sender_name = $existingPayment->transfer_sender_name;
            }
        }

        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentProof = null;
        $this->transfer_sender_name = '';
        $this->transfer_amount = '';
        $this->selectedFeeId = null;
    }

    public function submitPaymentProof(): void
    {
        $this->validate([
            'transfer_sender_name' => ['required', 'string', 'max:255'],
            'transfer_amount' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $amount = (float) str_replace(',', '.', (string) $value);
                    if ($amount <= 0) {
                        $fail('El monto transferido debe ser mayor a cero.');
                    }
                },
            ],
            'paymentProof' => ['required', 'file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:2048'],
        ], [
            'transfer_sender_name.required' => 'El nombre del titular de la cuenta origen es obligatorio.',
            'transfer_sender_name.max' => 'El nombre no puede superar los 255 caracteres.',
            'transfer_amount.required' => 'Debe indicar el monto transferido.',
            'paymentProof.required' => 'Debe adjuntar el comprobante.',
            'paymentProof.file' => 'El comprobante debe ser un archivo válido.',
            'paymentProof.mimetypes' => 'El archivo debe ser una imagen (JPG/PNG) o un PDF.',
            'paymentProof.image' => 'El archivo debe ser una imagen (JPG/PNG) o un PDF.',
            'paymentProof.max' => 'El archivo no puede superar los 2MB.',
        ]);

        /** @var Fee $fee */
        $fee = Fee::with('student')
            ->where('id', $this->selectedFeeId)
            ->whereIn('status', ['pending', 'partial'])
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
        $amountTransferred = (float) str_replace(',', '.', $this->transfer_amount);

        $paymentPayload = [
            'amount_reported' => $amountTransferred,
            'paid_on_date' => now()->toDateString(),
            'status' => 'pending_review',
            'evidence_file_path' => $path,
            'evidence_file_size' => Storage::disk(config('filesystems.default'))->size($path),
            'evidence_mime_type' => $this->paymentProof->getClientMimeType(),
            'transfer_sender_name' => trim($this->transfer_sender_name),
            'bank_reference' => null,
            'admin_comment' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];

        Payment::create(array_merge($paymentPayload, [
            'fee_id' => $fee->id,
            'tutor_id' => $tutor->id,
        ]));

        if ($fee->status !== 'partial') {
            $fee->update(['status' => 'pending']);
        }

        $this->closePaymentModal();

        session()->flash(
            'status',
            'Comprobante enviado. La administración revisará la transferencia.'
        );
    }
}

