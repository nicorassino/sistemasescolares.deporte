<?php

namespace App\Livewire\Admin;

use App\Mail\PaymentReminderMail;
use App\Models\Fee;
use App\Models\Group;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

class FeeManager extends Component
{
    public ?string $filterMonth = null;

    public ?string $filterYear = null;

    public ?int $filterGroupId = null;

    public ?string $filterStatus = null;

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Fee::query()
            ->with(['student.tutors.user', 'group'])
            ->orderBy('period')
            ->orderBy('due_date');

        if (trim((string) $this->filterMonth) !== '') {
            $query->whereRaw('SUBSTRING(period, 6, 2) = ?', [str_pad($this->filterMonth, 2, '0', STR_PAD_LEFT)]);
        }
        if (trim((string) $this->filterYear) !== '') {
            $query->whereRaw('SUBSTRING(period, 1, 4) = ?', [$this->filterYear]);
        }
        if ($this->filterGroupId) {
            $query->where('group_id', $this->filterGroupId);
        }
        if (trim((string) $this->filterStatus) !== '') {
            $query->where('status', $this->filterStatus);
        }

        $fees = $query->get();

        return view('livewire.admin.fee-manager', [
            'fees' => $fees,
            'groups' => Group::orderBy('name')->get(),
        ]);
    }

    public function sendReminder(int $feeId): void
    {
        $fee = Fee::with(['student.tutors.user'])->findOrFail($feeId);

        if (! in_array($fee->status, ['pending', 'partial'], true)) {
            session()->flash('error', 'Solo se pueden enviar recordatorios por cuotas pendientes o parciales.');
            return;
        }

        $tutor = $fee->student->tutors()->wherePivot('is_primary', true)->first()
            ?? $fee->student->tutors()->first();

        if (! $tutor) {
            session()->flash('error', 'El alumno no tiene tutor asignado.');
            return;
        }

        $tutor->load('user');
        $email = $tutor->user?->email;
        if (! $email) {
            session()->flash('error', 'El tutor no tiene email de acceso configurado.');
            return;
        }

        Mail::to($email)->send(new PaymentReminderMail($fee));
        $fee->update(['last_reminder_sent_at' => now()]);

        session()->flash('status', 'Recordatorio enviado correctamente.');
    }
}
