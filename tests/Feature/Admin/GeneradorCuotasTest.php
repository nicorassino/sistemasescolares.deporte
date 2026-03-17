<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\GeneradorCuotasMasivo;
use App\Models\Fee;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Generador de Cuotas Masivo del Administrador
 * Ruta: GET /admin/generador-cuotas
 * Componente: App\Livewire\Admin\GeneradorCuotasMasivo
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/GeneradorCuotasTest.php
 */
class GeneradorCuotasTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeActiveStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'first_name' => 'Alumno',
            'last_name' => 'Activo',
            'dni' => '12345678',
            'birth_date' => '2010-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ], $overrides));
    }

    protected function makeGroup(): Group
    {
        return Group::create(['name' => 'Grupo Generador', 'year' => 2026, 'is_active' => true]);
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_generador_cuotas_renderiza_correctamente(): void
    {
        $response = $this->get('/admin/generador-cuotas');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_componente_muestra_los_grupos_disponibles(): void
    {
        $group = $this->makeGroup();

        Livewire::test(GeneradorCuotasMasivo::class)
            ->assertSee('Grupo Generador');
    }

    // -------------------------------------------------------------------------
    // Tests de validación
    // -------------------------------------------------------------------------

    /** @test */
    public function generar_cuotas_falla_sin_monto(): void
    {
        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 3)
            ->set('anio', 2026)
            ->set('monto_base', '')
            ->set('fecha_vencimiento', '2026-03-31')
            ->call('generar')
            ->assertHasErrors(['monto_base' => 'required']);
    }

    /** @test */
    public function generar_cuotas_falla_sin_fecha_vencimiento(): void
    {
        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 3)
            ->set('anio', 2026)
            ->set('monto_base', '5000')
            ->set('fecha_vencimiento', '')
            ->call('generar')
            ->assertHasErrors(['fecha_vencimiento' => 'required']);
    }

    /** @test */
    public function generar_cuotas_falla_con_mes_invalido(): void
    {
        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 13)
            ->set('anio', 2026)
            ->set('monto_base', '5000')
            ->set('fecha_vencimiento', '2026-03-31')
            ->call('generar')
            ->assertHasErrors(['mes']);
    }

    /** @test */
    public function generar_cuotas_falla_con_grupo_inexistente(): void
    {
        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 3)
            ->set('anio', 2026)
            ->set('monto_base', '5000')
            ->set('fecha_vencimiento', '2026-03-31')
            ->set('grupo_id', 99999)
            ->call('generar')
            ->assertHasErrors(['grupo_id']);
    }

    // -------------------------------------------------------------------------
    // Tests de generación de cuotas
    // -------------------------------------------------------------------------

    /** @test */
    public function genera_cuotas_para_todos_los_alumnos_activos(): void
    {
        $this->makeActiveStudent(['first_name' => 'Alumno1', 'dni' => '11111111']);
        $this->makeActiveStudent(['first_name' => 'Alumno2', 'dni' => '22222222']);
        // Alumno inactivo que no debe recibir cuota
        $this->makeActiveStudent(['first_name' => 'Inactivo', 'dni' => '33333333', 'is_active' => false]);

        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 4)
            ->set('anio', 2026)
            ->set('monto_base', '5000')
            ->set('fecha_vencimiento', '2026-04-30')
            ->call('generar')
            ->assertHasNoErrors()
            ->assertSet('cuotas_generadas', 2);

        $this->assertEquals(2, Fee::count());
        $this->assertDatabaseHas('fees', ['period' => '2026-04', 'amount' => 5000.00]);
    }

    /** @test */
    public function genera_cuotas_solo_para_alumnos_del_grupo_seleccionado(): void
    {
        $group = $this->makeGroup();
        $student1 = $this->makeActiveStudent(['first_name' => 'EnGrupo', 'dni' => '44444444']);
        $student2 = $this->makeActiveStudent(['first_name' => 'SinGrupo', 'dni' => '55555555']);

        // Asociar student1 al grupo
        $group->students()->attach($student1->id, [
            'from_date' => '2026-01-01',
            'is_current' => true,
        ]);

        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 5)
            ->set('anio', 2026)
            ->set('monto_base', '4500')
            ->set('fecha_vencimiento', '2026-05-31')
            ->set('grupo_id', $group->id)
            ->call('generar')
            ->assertHasNoErrors()
            ->assertSet('cuotas_generadas', 1);

        // Solo se generó para el alumno del grupo
        $this->assertDatabaseHas('fees', ['student_id' => $student1->id, 'period' => '2026-05']);
        $this->assertDatabaseMissing('fees', ['student_id' => $student2->id, 'period' => '2026-05']);
    }

    /** @test */
    public function no_genera_duplicados_para_el_mismo_periodo(): void
    {
        $student = $this->makeActiveStudent(['first_name' => 'NoDuplicar', 'dni' => '66666666']);
        $group = $this->makeGroup();

        // Primera generación
        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 6)
            ->set('anio', 2026)
            ->set('monto_base', '5000')
            ->set('fecha_vencimiento', '2026-06-30')
            ->call('generar');

        // Segunda generación del mismo periodo
        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 6)
            ->set('anio', 2026)
            ->set('monto_base', '5000')
            ->set('fecha_vencimiento', '2026-06-30')
            ->call('generar')
            ->assertSet('cuotas_generadas', 0)
            ->assertSet('cuotas_omitidas', 1);

        $this->assertEquals(1, Fee::where('period', '2026-06')->count());
    }

    /** @test */
    public function aplica_beca_al_calcular_el_monto(): void
    {
        // Alumno con 50% de beca
        $student = $this->makeActiveStudent([
            'first_name' => 'Becado',
            'last_name' => 'Alumno',
            'dni' => '77777777',
            'scholarship_percentage' => 50,
        ]);

        Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 7)
            ->set('anio', 2026)
            ->set('monto_base', '6000')
            ->set('fecha_vencimiento', '2026-07-31')
            ->call('generar')
            ->assertHasNoErrors();

        // El monto debe ser 3000 (50% de descuento)
        $this->assertDatabaseHas('fees', [
            'student_id' => $student->id,
            'period' => '2026-07',
            'amount' => 3000.00,
        ]);
    }

    /** @test */
    public function muestra_el_contador_de_cuotas_generadas_y_omitidas(): void
    {
        $this->makeActiveStudent(['first_name' => 'ContA', 'dni' => '88888888']);
        $this->makeActiveStudent(['first_name' => 'ContB', 'dni' => '99999999']);

        $component = Livewire::test(GeneradorCuotasMasivo::class)
            ->set('mes', 8)
            ->set('anio', 2026)
            ->set('monto_base', '5000')
            ->set('fecha_vencimiento', '2026-08-31')
            ->call('generar');

        $component->assertSet('cuotas_generadas', 2);
        $component->assertSet('cuotas_omitidas', 0);
    }
}
