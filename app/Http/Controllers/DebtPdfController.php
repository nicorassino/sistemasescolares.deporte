<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DebtPdfController extends Controller
{
    /**
     * PDF con la deuda total de un alumno (todas las cuotas pendientes/parciales y su detalle).
     */
    public function student(Request $request, Student $student)
    {
        $fees = $student->fees()
            ->with('group')
            ->orderByDesc('period')
            ->get();

        $totalDebt = 0;
        $rows = [];
        foreach ($fees as $fee) {
            $owed = $this->amountOwed($fee);
            $totalDebt += $owed;
            $rows[] = (object) [
                'period' => $fee->period,
                'period_label' => \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y'),
                'amount' => $fee->amount,
                'paid_amount' => $fee->paid_amount ?? 0,
                'status' => $fee->status,
                'owed' => $owed,
            ];
        }

        $pdf = Pdf::loadView('pdf.debt-student', [
            'student' => $student,
            'rows' => $rows,
            'totalDebt' => $totalDebt,
            'generatedAt' => now(),
            'logoJuvenilia' => public_path('IMG/logo_juvenilia.jpeg'),
            'logoDeporte' => public_path('IMG/logodepte.jpeg'),
        ]);

        $filename = 'deuda-alumno-' . $student->id . '-' . $student->last_name . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * PDF con la deuda total de un grupo (por alumno y total del grupo).
     */
    public function group(Request $request, Group $group)
    {
        $fees = $group->fees()
            ->with('student')
            ->orderBy('student_id')
            ->orderByDesc('period')
            ->get();

        $byStudent = $fees->groupBy('student_id');
        $studentRows = [];
        $groupTotal = 0;

        foreach ($byStudent as $studentId => $studentFees) {
            $student = $studentFees->first()->student;
            $subTotal = 0;
            $conceptPeriods = [];
            foreach ($studentFees as $fee) {
                $owed = $this->amountOwed($fee);
                $subTotal += $owed;
                if ($owed > 0) {
                    $conceptPeriods[] = \Carbon\Carbon::createFromFormat('Y-m', $fee->period)->translatedFormat('F Y');
                }
            }
            $groupTotal += $subTotal;
            $studentRows[] = (object) [
                'student' => $student,
                'concept' => implode(', ', $conceptPeriods),
                'saldo' => $subTotal,
            ];
        }

        $pdf = Pdf::loadView('pdf.debt-group', [
            'group' => $group,
            'studentRows' => $studentRows,
            'groupTotal' => $groupTotal,
            'generatedAt' => now(),
            'logoJuvenilia' => public_path('IMG/logo_juvenilia.jpeg'),
            'logoDeporte' => public_path('IMG/logodepte.jpeg'),
        ]);

        $filename = 'deuda-grupo-' . $group->id . '-' . \Illuminate\Support\Str::slug($group->name) . '.pdf';

        return $pdf->download($filename);
    }

    private function amountOwed($fee): float
    {
        if ($fee->status === 'paid') {
            return 0;
        }
        if ($fee->status === 'partial' && $fee->paid_amount) {
            return (float) $fee->amount - (float) $fee->paid_amount;
        }

        return (float) $fee->amount;
    }
}
