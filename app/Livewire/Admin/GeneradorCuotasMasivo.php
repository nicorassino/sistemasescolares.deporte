<?php

namespace App\Livewire\Admin;

use App\Models\Fee;
use App\Models\Group;
use App\Models\Student;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GeneradorCuotasMasivo extends Component
{
    public int $mes = 1;

    public int $anio = 2026;

    public string $monto_base = '';

    public string $fecha_vencimiento = '';

    /** @var int|null null = todos los alumnos activos */
    public ?int $grupo_id = null;

    public ?int $cuotas_generadas = null;

    public ?int $cuotas_omitidas = null;

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.generador-cuotas-masivo', [
            'grupos' => Group::orderBy('name')->get(),
        ]);
    }

    public function generar(): void
    {
        $this->validate([
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'anio' => ['required', 'integer', 'min:2020', 'max:2030'],
            'monto_base' => ['required', 'numeric', 'min:0'],
            'fecha_vencimiento' => ['required', 'date'],
            'grupo_id' => ['nullable', 'integer', 'exists:groups,id'],
        ], [
            'mes.required' => 'El mes es obligatorio.',
            'anio.required' => 'El año es obligatorio.',
            'monto_base.required' => 'El monto base es obligatorio.',
            'monto_base.numeric' => 'El monto debe ser un número.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
        ]);

        $periodo = sprintf('%04d-%02d', $this->anio, $this->mes);
        $monto = (float) $this->monto_base;
        $vencimiento = $this->fecha_vencimiento;

        $alumnos = $this->obtenerAlumnos();

        $generadas = 0;
        $omitidas = 0;

        foreach ($alumnos as $alumno) {
            $fee = Fee::firstOrCreate(
                [
                    'student_id' => $alumno->id,
                    'period' => $periodo,
                ],
                [
                    'group_id' => $this->grupo_id ?: $alumno->groups->first(fn ($g) => (bool) $g->pivot?->is_current)?->id,
                    'type' => 'tuition',
                    'amount' => $monto,
                    'due_date' => $vencimiento,
                    'status' => 'pending',
                    'issued_at' => now(),
                ]
            );

            if ($fee->wasRecentlyCreated) {
                $generadas++;
            } else {
                $omitidas++;
            }
        }

        $this->cuotas_generadas = $generadas;
        $this->cuotas_omitidas = $omitidas;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Student>
     */
    protected function obtenerAlumnos()
    {
        if ($this->grupo_id === null) {
            return Student::with('groups')
                ->where('is_active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return Group::findOrFail($this->grupo_id)
            ->students()
            ->wherePivot('is_current', true)
            ->where('students.is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}
