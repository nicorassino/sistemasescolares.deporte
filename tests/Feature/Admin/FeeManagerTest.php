<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\FeeManager;
use App\Models\Fee;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Gestor de Cuotas (Deudas) del Administrador
 * Ruta: GET /admin/deudas
 * Componente: App\Livewire\Admin\FeeManager
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/FeeManagerTest.php
 */
class FeeManagerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeStudentWithFee(array $studentOverrides = [], array $feeOverrides = []): array
    {
        $student = Student::create(array_merge([
            'first_name' => 'Alumno',
            'last_name' => 'Test',
            'dni' => '12345678',
            'birth_date' => '2010-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ], $studentOverrides));

        $group = Group::create(['name' => 'Grupo Fee', 'year' => 2026, 'is_active' => true]);

        $fee = Fee::create(array_merge([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => '2026-03',
            'amount' => 5000,
            'paid_amount' => 0,
            'due_date' => '2026-03-31',
            'status' => 'pending',
            'issued_at' => now(),
        ], $feeOverrides));

        return compact('student', 'group', 'fee');
    }

    protected function makeTutorForStudent(Student $student): Tutor
    {
        $user = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $user->id,
            'first_name' => 'Tutor',
            'last_name' => 'Del Alumno',
            'phone_main' => '1199998888',
        ]);
        $student->tutors()->attach($tutor->id, ['is_primary' => true, 'relationship_type' => 'parent']);

        return $tutor;
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_deudas_renderiza_correctamente(): void
    {
        $response = $this->get('/admin/deudas');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_componente_lista_cuotas_existentes(): void
    {
        $setup = $this->makeStudentWithFee(['last_name' => 'ListarTest', 'dni' => '11223344']);

        Livewire::test(FeeManager::class)
            ->assertSee('ListarTest');
    }

    // -------------------------------------------------------------------------
    // Tests de filtros
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_filtrar_cuotas_por_mes(): void
    {
        $setup = $this->makeStudentWithFee(['last_name' => 'MesFiltro', 'dni' => '22334455'], ['period' => '2026-03']);

        Livewire::test(FeeManager::class)
            ->set('filterMonth', '03')
            ->assertSee('MesFiltro');
    }

    /** @test */
    public function puede_filtrar_cuotas_por_anio(): void
    {
        $setup = $this->makeStudentWithFee(['last_name' => 'AnioFiltro', 'dni' => '33445566'], ['period' => '2026-05']);

        Livewire::test(FeeManager::class)
            ->set('filterYear', '2026')
            ->assertSee('AnioFiltro');
    }

    /** @test */
    public function puede_filtrar_cuotas_por_estado(): void
    {
        $setup = $this->makeStudentWithFee(['last_name' => 'EstadoFiltro', 'dni' => '44556677'], ['status' => 'paid', 'paid_amount' => 5000]);

        Livewire::test(FeeManager::class)
            ->set('filterStatus', 'paid')
            ->assertSee('EstadoFiltro');
    }

    /** @test */
    public function puede_filtrar_cuotas_por_grupo(): void
    {
        $setup = $this->makeStudentWithFee(['last_name' => 'GrupoFiltro', 'dni' => '55667788']);

        Livewire::test(FeeManager::class)
            ->set('filterGroupId', $setup['group']->id)
            ->assertSee('GrupoFiltro');
    }

    // -------------------------------------------------------------------------
    // Tests de búsqueda por alumno/tutor
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_buscar_cuotas_por_apellido_de_alumno(): void
    {
        $setup = $this->makeStudentWithFee(['last_name' => 'Hernández', 'first_name' => 'Buscar', 'dni' => '10203040']);
        $setup2 = $this->makeStudentWithFee(['last_name' => 'OtroApellido', 'first_name' => 'Oculto', 'dni' => '50607080']);

        Livewire::test(FeeManager::class)
            ->set('studentSearch', 'Hernández')
            ->assertSee('Hernández')
            ->assertDontSee('OtroApellido');
    }

    /** @test */
    public function puede_buscar_cuotas_por_dni_de_alumno(): void
    {
        $setup = $this->makeStudentWithFee(['last_name' => 'DniBusqueda', 'dni' => '12121212']);

        Livewire::test(FeeManager::class)
            ->set('studentSearch', '12121212')
            ->assertSee('DniBusqueda');
    }

    /** @test */
    public function buscar_por_tutor_muestra_cuotas_de_alumnos_compartidos(): void
    {
        // Crear dos alumnos
        $setup1 = $this->makeStudentWithFee(['last_name' => 'Compartido1', 'first_name' => 'Alumno', 'dni' => '31415926']);
        $setup2 = $this->makeStudentWithFee(['last_name' => 'Compartido2', 'first_name' => 'Alumno', 'dni' => '27182818']);

        // Crear un tutor compartido y asignar a ambos alumnos
        $tutorUser = User::factory()->create(['role' => 'tutor', 'email' => 'tutor.compartido@test.com']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'first_name' => 'Tutor',
            'last_name' => 'Compartido',
            'phone_main' => '1100110011',
            'dni' => '88776655',
        ]);
        $setup1['student']->tutors()->attach($tutor->id, ['is_primary' => true, 'relationship_type' => 'parent']);
        $setup2['student']->tutors()->attach($tutor->id, ['is_primary' => false, 'relationship_type' => 'parent']);

        // Buscar por DNI del tutor debe mostrar ambos alumnos
        Livewire::test(FeeManager::class)
            ->set('studentSearch', '88776655')
            ->assertSee('Compartido1')
            ->assertSee('Compartido2');
    }

    /** @test */
    public function busqueda_vacia_muestra_todas_las_cuotas(): void
    {
        $setup1 = $this->makeStudentWithFee(['last_name' => 'TodosA', 'dni' => '11112222']);
        $setup2 = $this->makeStudentWithFee(['last_name' => 'TodosB', 'dni' => '33334444']);

        Livewire::test(FeeManager::class)
            ->set('studentSearch', '')
            ->assertSee('TodosA')
            ->assertSee('TodosB');
    }

    // -------------------------------------------------------------------------
    // Tests de envío de recordatorio
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_enviar_recordatorio_a_tutor_para_cuota_pendiente(): void
    {
        Mail::fake();

        $setup = $this->makeStudentWithFee(['last_name' => 'RecordatorioTest', 'dni' => '77665544']);
        $tutor = $this->makeTutorForStudent($setup['student']);

        Livewire::test(FeeManager::class)
            ->call('sendReminder', $setup['fee']->id)
            ->assertSessionHas('status');
    }

    /** @test */
    public function no_puede_enviar_recordatorio_por_cuota_ya_pagada(): void
    {
        Mail::fake();

        $setup = $this->makeStudentWithFee(
            ['last_name' => 'YaPagado', 'dni' => '99887755'],
            ['status' => 'paid', 'paid_amount' => 5000]
        );

        Livewire::test(FeeManager::class)
            ->call('sendReminder', $setup['fee']->id)
            ->assertSessionHas('error');
    }

    /** @test */
    public function no_puede_enviar_recordatorio_si_alumno_no_tiene_tutor(): void
    {
        Mail::fake();

        $setup = $this->makeStudentWithFee(['last_name' => 'SinTutor', 'dni' => '99001122']);

        Livewire::test(FeeManager::class)
            ->call('sendReminder', $setup['fee']->id)
            ->assertSessionHas('error');
    }
}
