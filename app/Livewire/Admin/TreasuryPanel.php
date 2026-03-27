<?php

namespace App\Livewire\Admin;

use App\Mail\PaymentApprovedMail;
use App\Models\Group;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        $payment = Payment::with(['fee.student', 'tutor.user'])->findOrFail($paymentId);

        if ($payment->status !== 'pending_review') {
            session()->flash('warning', 'Este pago ya no está en revisión.');
            return;
        }

        $summary = DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status !== 'pending_review') {
                return null;
            }

            $fee = $lockedPayment->fee()->lockForUpdate()->firstOrFail();

            $approvedPayments = Payment::query()
                ->where('fee_id', $fee->id)
                ->where('status', 'approved')
                ->orderBy('id')
                ->get();

            $oldAllocation = $this->allocateFeePayments($approvedPayments, (float) $fee->amount);
            $newApproved = $approvedPayments->push($lockedPayment);
            $newAllocation = $this->allocateFeePayments($newApproved, (float) $fee->amount);

            if (! $fee->receipt_number) {
                $fee->receipt_number = $this->generateReceiptNumber($fee->id);
            }

            $lockedPayment->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            $this->applyFeeFromAllocation($fee, $newAllocation);
            $this->applyWalletCreditDelta($oldAllocation['credits_by_tutor'], $newAllocation['credits_by_tutor']);

            return [
                'applied' => $newAllocation['by_payment'][$lockedPayment->id]['applied'] ?? 0.0,
                'credit' => $newAllocation['by_payment'][$lockedPayment->id]['credit'] ?? 0.0,
                'fee_status' => $fee->status,
                'remaining' => $newAllocation['remaining'] ?? 0.0,
            ];
        });

        if ($summary === null) {
            session()->flash('warning', 'Este pago ya fue revisado por otro administrador.');
            return;
        }

        $tutorEmail = $payment->tutor->user?->email;
        if (! $tutorEmail) {
            session()->flash('warning', $this->approvalMessage($summary).' No se envió correo: el tutor no tiene email configurado.');
            return;
        }

        try {
            Mail::to($tutorEmail)->send(new PaymentApprovedMail(
                $payment->fee,
                (float) ($summary['applied'] ?? 0),
                (float) ($summary['remaining'] ?? 0)
            ));
            session()->flash('status', $this->approvalMessage($summary).' Correo enviado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error enviando PaymentApprovedMail desde tesorería', [
                'payment_id' => $payment->id,
                'tutor_email' => $tutorEmail,
                'exception' => $e->getMessage(),
            ]);

            session()->flash('warning', $this->approvalMessage($summary).' No se pudo enviar el correo. Revisá SMTP y logs.');
        }
    }

    protected function generateReceiptNumber(int $feeId): string
    {
        $datePart = now()->format('Ymd');
        $sequencePart = str_pad((string) $feeId, 6, '0', STR_PAD_LEFT);

        do {
            $securePart = Str::upper(Str::random(6));
            $candidate = "REC-{$datePart}-{$sequencePart}-{$securePart}";
        } while (\App\Models\Fee::where('receipt_number', $candidate)->exists());

        return $candidate;
    }

    public function rejectPayment(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);

        if ($payment->status !== 'pending_review') {
            session()->flash('warning', 'Este pago ya no está en revisión.');
            return;
        }

        $payment->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('status', 'Pago rechazado. La cuota mantiene su estado actual.');
    }

    public function resetToPending(int $paymentId): void
    {
        $payment = Payment::with('fee')->findOrFail($paymentId);

        if (! in_array($payment->status, ['approved', 'rejected'], true)) {
            session()->flash('warning', 'Solo se puede volver a revisión pagos aprobados o rechazados.');
            return;
        }

        DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $fee = $lockedPayment->fee()->lockForUpdate()->firstOrFail();

            $approvedBefore = Payment::query()
                ->where('fee_id', $fee->id)
                ->where('status', 'approved')
                ->orderBy('id')
                ->get();

            $oldAllocation = $this->allocateFeePayments($approvedBefore, (float) $fee->amount);

            $lockedPayment->update([
                'status' => 'pending_review',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);

            $approvedAfter = Payment::query()
                ->where('fee_id', $fee->id)
                ->where('status', 'approved')
                ->orderBy('id')
                ->get();

            $newAllocation = $this->allocateFeePayments($approvedAfter, (float) $fee->amount);

            $this->applyFeeFromAllocation($fee, $newAllocation);
            $this->applyWalletCreditDelta($oldAllocation['credits_by_tutor'], $newAllocation['credits_by_tutor']);
        });

        session()->flash('status', 'El pago volvió al estado de revisión.');
    }

    private function allocateFeePayments(Collection $payments, float $feeAmount): array
    {
        $remaining = max($feeAmount, 0);
        $appliedTotal = 0.0;
        $creditsByTutor = [];
        $byPayment = [];

        foreach ($payments as $payment) {
            $amount = (float) $payment->amount_reported;
            $applied = min($amount, $remaining);
            $credit = max($amount - $applied, 0);

            $remaining -= $applied;
            $appliedTotal += $applied;

            if ($credit > 0 && $payment->tutor_id) {
                $creditsByTutor[$payment->tutor_id] = ($creditsByTutor[$payment->tutor_id] ?? 0) + $credit;
            }

            $byPayment[$payment->id] = [
                'applied' => $applied,
                'credit' => $credit,
            ];
        }

        return [
            'applied_total' => $appliedTotal,
            'remaining' => max($remaining, 0),
            'credits_by_tutor' => $creditsByTutor,
            'by_payment' => $byPayment,
        ];
    }

    private function applyFeeFromAllocation($fee, array $allocation): void
    {
        $applied = min((float) $allocation['applied_total'], (float) $fee->amount);
        $remaining = max((float) $fee->amount - $applied, 0);

        $fee->paid_amount = $applied;
        if ($remaining <= 0.00001) {
            $fee->status = 'paid';
            $fee->paid_at = Carbon::now();
        } elseif ($applied > 0) {
            $fee->status = 'partial';
            $fee->paid_at = null;
        } else {
            $fee->status = 'pending';
            $fee->paid_at = null;
        }

        $fee->save();
    }

    private function applyWalletCreditDelta(array $oldCredits, array $newCredits): void
    {
        $tutorIds = array_unique(array_merge(array_keys($oldCredits), array_keys($newCredits)));

        foreach ($tutorIds as $tutorId) {
            $old = (float) ($oldCredits[$tutorId] ?? 0);
            $new = (float) ($newCredits[$tutorId] ?? 0);
            $delta = $new - $old;

            if (abs($delta) < 0.00001) {
                continue;
            }

            $tutor = \App\Models\Tutor::query()->lockForUpdate()->find($tutorId);
            if (! $tutor) {
                continue;
            }

            $newBalance = (float) $tutor->wallet_balance + $delta;
            $tutor->wallet_balance = max($newBalance, 0);
            $tutor->save();
        }
    }

    private function approvalMessage(array $summary): string
    {
        $applied = number_format((float) $summary['applied'], 2, ',', '.');
        $credit = number_format((float) $summary['credit'], 2, ',', '.');

        if ((float) $summary['credit'] > 0) {
            return "Pago aprobado: se aplicaron $ {$applied} a la cuota y se acreditaron $ {$credit} en billetera.";
        }

        if ($summary['fee_status'] === 'partial') {
            return "Pago aprobado parcialmente: se aplicaron $ {$applied}.";
        }

        return "Pago aprobado: se aplicaron $ {$applied} y la cuota quedó saldada.";
    }
}
