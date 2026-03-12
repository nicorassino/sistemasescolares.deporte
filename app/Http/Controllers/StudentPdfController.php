<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentPdfController extends Controller
{
    /**
     * Listado de alumnos por grupo (nombre, apellido, DNI, fecha de nacimiento).
     * Query: ?group=id para filtrar por un grupo, sin param para todos.
     */
    public function groups()
    {
        $query = Group::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
        }])->orderBy('name');

        $groupId = request()->query('group');
        if ($groupId && is_numeric($groupId)) {
            $query->where('id', (int) $groupId);
        }

        $groups = $query->get();

        $pdf = Pdf::loadView('pdf.students-by-group', [
            'groups' => $groups,
            'generatedAt' => now(),
            'logoJuvenilia' => public_path('IMG/logo_juvenilia.jpeg'),
            'logoDeporte' => public_path('IMG/logodepte.jpeg'),
        ]);

        $filename = 'alumnos-por-grupo-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}

