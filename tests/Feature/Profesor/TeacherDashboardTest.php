<?php

namespace Tests\Feature\Profesor;

use App\Livewire\Teacher\TeacherDashboard;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Panel del Profesor (Asistencia + Cobros)
 * Ruta: GET /profesor (con auth)
 * Componente: App\Livewire\Teacher\TeacherDashboard
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Profesor/TeacherDashboardTest.php
 */
class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function setupTeacherWithGroupAndStudents(): array
    {
        try {
            $teacherUser = User::factory()->create([
                'email' => 'profe.dashboard@test.com',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]);
        } catch (QueryException $e) {
            if (DB::getDriverName() === 'sqlite' && str_contains($e->getMessage(), 'CHECK constraint failed: role')) {
                $this->markTestSkipped('SQLite users.role no permite role=teacher (CHECK constraint).');
            }

            throw $e;
        }

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'Profesor',
            'last_name' => 'Dashboard',
            'is_active' => true,
        ]);

        $group = Group::create([
            'name' => 'Grupo Profesor',
            'year' => 2026,
            'is_active' => true,
            'teacher_id' => $teacher->id,
        ]);

        $student = Student::create([
            'first_name' => 'Alumno',
            'last_name' => 'Asistencia',
            'dni' => '55443322',
            'birth_date' => '2011-05-15',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $group->students()->attach($student->id, [
            'from_date' => '2026-01-01',
            'is_current' => true,
        ]);

        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'first_name' => 'Tutor',
            'last_name' => 'DelAlumno',
            'phone_main' => '1199009900',
        ]);
        $student->tutors()->attach($tutor->id, ['is_primary' => true, 'relationship_type' => 'parent']);

        $fee = Fee::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => '2026-03',
            'amount' => 5000,
            'paid_amount' => 0,
            'due_date' => '2026-03-31',
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        return compact('teacherUser', 'teacher', 'group', 'student', 'tutor', 'fee');
    }

    // -------------------------------------------------------------------------
    // Tests de acceso
    // -------------------------------------------------------------------------

    /** @test */
    public function visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get('/profesor');

        $response->assertRedirect();
    }

    /** @test */
    public function profesor_autenticado_puede_acceder_al_panel(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        $response = $this->actingAs($setup['teacherUser'])->get('/profesor');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Tests de selección de grupo y fecha
    // -------------------------------------------------------------------------

    /** @test */
    public function el_profesor_ve_sus_grupos(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->assertSee('Grupo Profesor');
    }

    /** @test */
    public function al_seleccionar_un_grupo_se_muestran_los_alumnos(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->assertSee('Asistencia');
    }

    // -------------------------------------------------------------------------
    // Tests de asistencia
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_marcar_asistencia_como_presente(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->set('selectedDate', '2026-03-15')
            ->call('toggleAttendance', $setup['student']->id, 'P');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $setup['student']->id,
            'teacher_id' => $setup['teacher']->id,
            'date' => '2026-03-15',
            'status' => 'P',
        ]);
    }

    /** @test */
    public function puede_marcar_asistencia_como_ausente(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->set('selectedDate', '2026-03-15')
            ->call('toggleAttendance', $setup['student']->id, 'A');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $setup['student']->id,
            'teacher_id' => $setup['teacher']->id,
            'date' => '2026-03-15',
            'status' => 'A',
        ]);
    }

    /** @test */
    public function marcar_asistencia_con_estado_invalido_no_hace_nada(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->set('selectedDate', '2026-03-15')
            ->call('toggleAttendance', $setup['student']->id, 'X');

        $this->assertDatabaseMissing('attendances', [
            'student_id' => $setup['student']->id,
            'status' => 'X',
        ]);
    }

    /** @test */
    public function puede_cambiar_asistencia_de_presente_a_ausente(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        $component = Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->set('selectedDate', '2026-03-15');

        // Primero Presente
        $component->call('toggleAttendance', $setup['student']->id, 'P');
        $this->assertDatabaseHas('attendances', ['student_id' => $setup['student']->id, 'status' => 'P']);

        // Luego Ausente
        $component->call('toggleAttendance', $setup['student']->id, 'A');
        $this->assertDatabaseHas('attendances', ['student_id' => $setup['student']->id, 'status' => 'A']);
    }

    // -------------------------------------------------------------------------
    // Tests de cobro en efectivo
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_abrir_el_modal_de_cobro_en_efectivo(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->call('openCashModal', $setup['student']->id)
            ->assertSet('showCashModal', true)
            ->assertSet('cashStudentId', $setup['student']->id);
    }

    /** @test */
    public function puede_cerrar_el_modal_de_cobro(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->call('openCashModal', $setup['student']->id)
            ->call('closeCashModal')
            ->assertSet('showCashModal', false);
    }

    /** @test */
    public function puede_registrar_cobro_en_efectivo_que_sald_a_la_cuota(): void
    {
        Mail::fake();

        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->call('openCashModal', $setup['student']->id)
            ->set('cashAmount', '5000')
            ->call('processCashPayment')
            ->assertHasNoErrors()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('fees', [
            'id' => $setup['fee']->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'fee_id' => $setup['fee']->id,
            'status' => 'approved',
            'amount_reported' => 5000,
        ]);
    }

    /** @test */
    public function cobro_parcial_deja_la_cuota_en_estado_partial(): void
    {
        Mail::fake();

        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->call('openCashModal', $setup['student']->id)
            ->set('cashAmount', '2000')
            ->call('processCashPayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fees', [
            'id' => $setup['fee']->id,
            'status' => 'partial',
            'paid_amount' => 2000,
        ]);
    }

    /** @test */
    public function cobro_en_efectivo_falla_sin_monto(): void
    {
        $setup = $this->setupTeacherWithGroupAndStudents();

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->call('openCashModal', $setup['student']->id)
            ->set('cashAmount', '')
            ->call('processCashPayment')
            ->assertHasErrors(['cashAmount']);
    }

    /** @test */
    public function cobro_excedente_agrega_saldo_al_tutor(): void
    {
        Mail::fake();

        $setup = $this->setupTeacherWithGroupAndStudents();
        $initialWallet = (float) $setup['tutor']->wallet_balance;

        Livewire::actingAs($setup['teacherUser'])
            ->test(TeacherDashboard::class)
            ->set('selectedGroupId', $setup['group']->id)
            ->call('openCashModal', $setup['student']->id)
            ->set('cashAmount', '6000')  // 1000 de excedente
            ->call('processCashPayment')
            ->assertHasNoErrors();

        $setup['tutor']->refresh();
        $this->assertEquals($initialWallet + 1000, (float) $setup['tutor']->wallet_balance);
    }
}
