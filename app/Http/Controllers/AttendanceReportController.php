<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceReportController extends Controller
{
    public function adminGroupMonth(Request $request)
    {
        $group = $this->resolveGroup($request);
        $month = $this->resolveMonth($request);

        return $this->buildPdf($group, $month);
    }

    public function teacherGroupMonth(Request $request)
    {
        $group = $this->resolveGroup($request);
        $month = $this->resolveMonth($request);

        $teacher = Auth::user()?->teacher;
        if (! $teacher || ! $teacher->groups()->whereKey($group->id)->exists()) {
            abort(403);
        }

        return $this->buildPdf($group, $month, $teacher->id);
    }

    private function resolveGroup(Request $request): Group
    {
        $validated = $request->validate([
            'group' => ['required', 'integer', 'exists:groups,id'],
        ]);

        return Group::findOrFail((int) $validated['group']);
    }

    private function resolveMonth(Request $request): string
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        return (string) $validated['month'];
    }

    private function buildPdf(Group $group, string $month, ?int $teacherId = null)
    {
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $students = $group->students()
            ->wherePivot('is_current', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $attendanceQuery = Attendance::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date');

        if ($teacherId) {
            $attendanceQuery->where('teacher_id', $teacherId);
        }

        $attendances = $attendanceQuery->get();

        $rows = $students->map(function ($student) use ($attendances) {
            $studentAttendances = $attendances->where('student_id', $student->id);
            $presentCount = $studentAttendances->where('status', 'P')->count();
            $absentCount = $studentAttendances->where('status', 'A')->count();

            $detail = $studentAttendances
                ->map(function ($att) {
                    $date = \Carbon\Carbon::parse($att->date)->format('d/m');
                    return $date.' ('.$att->status.')';
                })
                ->implode(', ');

            return (object) [
                'student' => $student,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'total_marked' => $presentCount + $absentCount,
                'detail' => $detail,
            ];
        });

        $pdf = Pdf::loadView('pdf.attendance-by-group-month', [
            'group' => $group,
            'monthLabel' => $start->translatedFormat('F Y'),
            'rows' => $rows,
            'generatedAt' => now(),
            'logoJuvenilia' => public_path('IMG/logo_juvenilia.jpeg'),
            'logoDeporte' => public_path('IMG/logodepte.jpeg'),
        ]);

        $filename = 'asistencia-grupo-'.$group->id.'-'.$start->format('Y-m').'.pdf';

        return $pdf->download($filename);
    }
}
