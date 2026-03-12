<?php

namespace App\Livewire\Admin;

use App\Mail\PaymentApprovedMail;
use App\Models\Group;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TreasuryPanel extends Component
{
    /** @var string 'pending' | 'history' */
    public string $activeTab = 'pending';

    public ?int $filter_year = null;
    public ?int $filter_month = null;
    public ?int $filter_group_id = null;

    #[Layout('layouts.app')]
    public function render()
    {
        $basePending = Payment::query()
            ->where('status', 'pending_review')
            ->with(['fee.student', 'fee.group', 'tutor']);

        $baseHistory = Payment::query()
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['fee.student', 'fee.group', 'tutor', 'reviewer']);

        $this->applyFilters($basePending);
        $this->applyFilters($baseHistory);

        $pendingPayments = $basePending->orderBy('created_at')->get();
        $historyPayments = $baseHistory->orderByDesc('reviewed_at')->limit(100)->get();

        $years = Payment::query()
            ->join('fees', 'payments.fee_id', '=', 'fees.id')
            ->selectRaw('DISTINCT SUBSTRING(fees.period, 1, 4) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn ($y) => (int) $y);

        if ($years->isEmpty()) {
            $years = collect([(int) date('Y')]);
        }

        return view('livewire.admin.treasury-panel', [
            'payments' => $pendingPayments,
            'historyPayments' => $historyPayments,
            'groups' => Group::orderBy('name')->get(),
            'years' => $years,
        ]);
    }

    protected function applyFilters($query): void
    {
        $query->whereHas('fee', function ($q) {
            $hasYear = $this->filter_year !== null && $this->filter_year > 0;
            $hasMonth = $this->filter_month !== null && $this->filter_month >= 1 && $this->filter_month <= 12;

            if ($hasYear && $hasMonth) {
                $q->where('period', '=', sprintf('%04d-%02d', $this->filter_year, $this->filter_month));
            } else {
                if ($hasYear) {
                    $q->where('period', 'like', (string) $this->filter_year . '-%');
                }
                if ($hasMonth) {
                    $q->where('period', 'like', '%-' . str_pad((string) $this->filter_month, 2, '0', STR_PAD_LEFT));
                }
            }
            if ($this->filter_group_id !== null && $this->filter_group_id > 0) {
                $q->where('group_id', $this->filter_group_id);
            }
        });
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['pending', 'history'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function clearFilters(): void
    {
        $this->filter_year = null;
        $this->filter_month = null;
        $this->filter_group_id = null;
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
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $payment->fee->update([
            'status' => 'pending',
            'paid_at' => null,
        ]);

        session()->flash('status', 'Pago rechazado y comprobante eliminado.');
    }

    public function resetToPending(int $paymentId): void
    {
        $payment = Payment::whereIn('status', ['approved', 'rejected'])
            ->with('fee')
            ->findOrFail($paymentId);

        $wasApproved = $payment->status === 'approved';

        $payment->update([
            'status' => 'pending_review',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        if ($wasApproved) {
            $payment->fee->update([
                'status' => 'pending',
                'paid_at' => null,
            ]);
        }

        session()->flash('status', 'El pago volvió al estado de revisión.');
    }
}
