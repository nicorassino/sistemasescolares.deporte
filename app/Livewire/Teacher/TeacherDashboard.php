<?php

namespace App\Livewire\Teacher;

use App\Mail\PaymentApprovedMail;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TeacherDashboard extends Component
{
    public ?int $selectedGroupId = null;

    public string $selectedDate = '';
    public string $reportMonth = '';

    public bool $showCashModal = false;

    public ?int $cashStudentId = null;

    public string $cashAmount = '';

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->reportMonth = now()->format('Y-m');
    }

    #[Layout('layouts.teacher')]
    public function render()
    {
        $user = Auth::user();
        $teacher = $user?->teacher;

        $groups = $teacher
            ? $teacher->groups()->orderBy('name')->get()
            : collect();

        $students = collect();
        $attendancesByStudent = [];

        if ($this->selectedGroupId && $teacher) {
            $group = $teacher->groups()->find($this->selectedGroupId);
            if ($group) {
                $students = $group->students()
                    ->wherePivot('is_current', true)
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
                $attendancesByStudent = Attendance::query()
                    ->where('teacher_id', $teacher->id)
                    ->where('date', $this->selectedDate)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get()
                    ->keyBy('student_id');
            }
        }

        $cashFee = null;
        if ($this->cashStudentId && $this->selectedGroupId) {
            $cashFee = Fee::query()
                ->where('student_id', $this->cashStudentId)
                ->where('group_id', $this->selectedGroupId)
                ->whereIn('status', ['pending', 'partial'])
                ->orderBy('due_date')
                ->first();
        }

        return view('livewire.teacher.teacher-dashboard', [
            'teacher' => $teacher,
            'groups' => $groups,
            'students' => $students,
            'attendancesByStudent' => $attendancesByStudent,
            'cashFee' => $cashFee,
        ]);
    }

    public function toggleAttendance(int $studentId, string $status): void
    {
        if (! in_array($status, ['P', 'A'], true)) {
            return;
        }

        $teacher = Auth::user()?->teacher;
        if (! $teacher) {
            return;
        }

        Attendance::query()->updateOrCreate(
            [
                'student_id' => $studentId,
                'teacher_id' => $teacher->id,
                'date' => $this->selectedDate,
            ],
            ['status' => $status]
        );
    }

    public function openCashModal(int $studentId): void
    {
        $this->cashStudentId = $studentId;
        $this->cashAmount = '';
        $this->showCashModal = true;
    }

    public function closeCashModal(): void
    {
        $this->showCashModal = false;
        $this->cashStudentId = null;
        $this->cashAmount = '';
    }

    public function processCashPayment(): void
    {
        $this->validate([
            'cashAmount' => [
                'required',
                function (string $attr, mixed $value, \Closure $fail) {
                    $n = (float) str_replace(',', '.', (string) $value);
                    if ($n < 0.01) {
                        $fail('El monto debe ser mayor a cero.');
                    }
                },
            ],
        ], [
            'cashAmount.required' => 'Ingresá el monto recibido.',
        ]);

        $amount = (float) str_replace(',', '.', $this->cashAmount);
        $studentId = $this->cashStudentId;
        $teacher = Auth::user()?->teacher;
        if (! $teacher || ! $this->selectedGroupId) {
            session()->flash('error', 'Sesión o grupo no válido.');
            return;
        }

        $fee = Fee::query()
            ->where('student_id', $studentId)
            ->where('group_id', $this->selectedGroupId)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('due_date')
            ->lockForUpdate()
            ->first();

        if (! $fee) {
            session()->flash('error', 'No hay cuota pendiente para este alumno en este grupo.');
            $this->closeCashModal();
            return;
        }

        $debt = (float) $fee->amount - (float) $fee->paid_amount;
        $tutor = $fee->student->tutors()->wherePivot('is_primary', true)->first()
            ?? $fee->student->tutors()->first();

        if (! $tutor) {
            session()->flash('error', 'El alumno no tiene tutor asignado.');
            $this->closeCashModal();
            return;
        }

        $feeAmount = (float) $fee->amount;
        $currentPaid = (float) $fee->paid_amount;

        DB::transaction(function () use ($fee, $amount, $debt, $feeAmount, $currentPaid, $teacher, $tutor) {
            if ($amount >= $debt) {
                $fee->update([
                    'status' => 'paid',
                    'paid_amount' => $feeAmount,
                    'paid_at' => now(),
                ]);
                if ($amount > $debt) {
                    $change = $amount - $debt;
                    $tutor->increment('wallet_balance', $change);
                }
            } else {
                $fee->update([
                    'status' => 'partial',
                    'paid_amount' => $currentPaid + $amount,
                ]);
            }

            Payment::create([
                'fee_id' => $fee->id,
                'tutor_id' => $tutor->id,
                'teacher_id' => $teacher->id,
                'amount_reported' => $amount,
                'paid_on_date' => now(),
                'status' => 'approved',
                'evidence_file_path' => null,
                'evidence_file_size' => null,
                'evidence_mime_type' => null,
            ]);
        });

        $tutor->load('user');
        $tutorEmail = $tutor->user?->email;
        if ($tutorEmail) {
            $fee->refresh();
            $appliedAmount = min($amount, $debt);
            $remainingAmount = max((float) $fee->amount - (float) $fee->paid_amount, 0);
            Mail::to($tutorEmail)->send(new PaymentApprovedMail($fee, $appliedAmount, $remainingAmount));
        }

        session()->flash('status', 'Cobro registrado correctamente.');
        $this->closeCashModal();
    }
}
