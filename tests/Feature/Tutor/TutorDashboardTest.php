<?php

namespace Tests\Feature\Tutor;

use App\Livewire\Tutor\TutorDashboard;
use App\Models\Announcement;
use App\Models\Fee;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Dashboard del Tutor
 * Ruta: GET /tutor (con auth)
 * Componente: App\Livewire\Tutor\TutorDashboard
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Tutor/TutorDashboardTest.php
 */
class TutorDashboardTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function createTutorWithStudentAndFee(string $feeStatus = 'pending'): array
    {
        $tutorUser = User::factory()->create([
            'email' => 'tutor.dashboard@test.com',
            'password' => Hash::make('password'),
            'role' => 'tutor',
        ]);

        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'first_name' => 'Tutor',
            'last_name' => 'Dashboard',
            'phone_main' => '1199887766',
        ]);

        $student = Student::create([
            'first_name' => 'Alumno',
            'last_name' => 'Del Tutor',
            'dni' => '11223344',
            'birth_date' => '2011-07-20',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $tutor->students()->attach($student->id, ['is_primary' => true, 'relationship_type' => 'parent']);

        $group = Group::create(['name' => 'Grupo Tutor', 'year' => 2026, 'is_active' => true]);

        $fee = Fee::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => '2026-03',
            'amount' => 5000,
            'paid_amount' => 0,
            'due_date' => '2026-03-31',
            'status' => $feeStatus,
            'issued_at' => now(),
        ]);

        return compact('tutorUser', 'tutor', 'student', 'group', 'fee');
    }

    // -------------------------------------------------------------------------
    // Tests de acceso
    // -------------------------------------------------------------------------

    /** @test */
    public function visitante_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get('/tutor');

        $response->assertRedirect();
    }

    /** @test */
    public function tutor_autenticado_puede_acceder_al_dashboard(): void
    {
        $setup = $this->createTutorWithStudentAndFee();

        $response = $this->actingAs($setup['tutorUser'])->get('/tutor');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Tests de cuotas pendientes
    // -------------------------------------------------------------------------

    /** @test */
    public function el_tutor_ve_las_cuotas_pendientes_de_sus_alumnos(): void
    {
        $setup = $this->createTutorWithStudentAndFee('pending');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->assertSee('Del Tutor');
    }

    /** @test */
    public function el_tutor_no_ve_cuotas_de_alumnos_que_no_son_suyos(): void
    {
        $setup = $this->createTutorWithStudentAndFee('pending');

        // Crear otro alumno con otra cuota (sin relación con este tutor)
        $otherStudent = Student::create([
            'first_name' => 'Otro',
            'last_name' => 'Alumno',
            'dni' => '99887766',
            'birth_date' => '2012-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        // La vista del tutor solo debe mostrar su propio alumno
        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->assertSee('Del Tutor')
            ->assertDontSee('Otro');
    }

    // -------------------------------------------------------------------------
    // Tests de modal de cuotas pagadas
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_abrir_el_modal_de_cuotas_pagadas(): void
    {
        $setup = $this->createTutorWithStudentAndFee('paid');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->call('openPaidModal', $setup['student']->id)
            ->assertSet('showPaidModal', true)
            ->assertSet('paidStudentId', $setup['student']->id);
    }

    /** @test */
    public function puede_cerrar_el_modal_de_cuotas_pagadas(): void
    {
        $setup = $this->createTutorWithStudentAndFee('paid');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->call('openPaidModal', $setup['student']->id)
            ->call('closePaidModal')
            ->assertSet('showPaidModal', false)
            ->assertSet('paidStudentId', null);
    }

    // -------------------------------------------------------------------------
    // Tests de informar pago (subir comprobante)
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_abrir_el_modal_de_pago(): void
    {
        $setup = $this->createTutorWithStudentAndFee('pending');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->call('openPaymentModal', $setup['fee']->id)
            ->assertSet('showPaymentModal', true)
            ->assertSet('selectedFeeId', $setup['fee']->id);
    }

    /** @test */
    public function puede_cerrar_el_modal_de_pago(): void
    {
        $setup = $this->createTutorWithStudentAndFee('pending');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->call('openPaymentModal', $setup['fee']->id)
            ->call('closePaymentModal')
            ->assertSet('showPaymentModal', false)
            ->assertSet('selectedFeeId', null);
    }

    /** @test */
    public function puede_subir_comprobante_de_pago(): void
    {
        Storage::fake('local');
        $setup = $this->createTutorWithStudentAndFee('pending');
        $file = UploadedFile::fake()->image('comprobante.jpg');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->call('openPaymentModal', $setup['fee']->id)
            ->set('transfer_sender_name', 'Nombre Titular')
            ->set('paymentProof', $file)
            ->call('submitPaymentProof')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'fee_id' => $setup['fee']->id,
            'tutor_id' => $setup['tutor']->id,
            'status' => 'pending_review',
        ]);
    }

    /** @test */
    public function falla_al_informar_pago_sin_comprobante(): void
    {
        $setup = $this->createTutorWithStudentAndFee('pending');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->call('openPaymentModal', $setup['fee']->id)
            ->set('transfer_sender_name', 'Sin Comprobante')
            ->set('paymentProof', null)
            ->call('submitPaymentProof')
            ->assertHasErrors(['paymentProof']);
    }

    /** @test */
    public function falla_al_informar_pago_sin_nombre_titular(): void
    {
        Storage::fake('local');
        $setup = $this->createTutorWithStudentAndFee('pending');
        $file = UploadedFile::fake()->image('comprobante2.jpg');

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->call('openPaymentModal', $setup['fee']->id)
            ->set('transfer_sender_name', '')
            ->set('paymentProof', $file)
            ->call('submitPaymentProof')
            ->assertHasErrors(['transfer_sender_name']);
    }

    // -------------------------------------------------------------------------
    // Tests de novedades
    // -------------------------------------------------------------------------

    /** @test */
    public function el_tutor_ve_las_novedades_publicadas(): void
    {
        $setup = $this->createTutorWithStudentAndFee();

        $admin = User::factory()->create(['role' => 'admin']);
        Announcement::create([
            'title' => 'Novedad Visible Tutor',
            'content' => 'Contenido para el tutor.',
            'author_id' => $admin->id,
        ]);

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->assertSee('Novedad Visible Tutor');
    }

    // -------------------------------------------------------------------------
    // Tests de navegación por secciones
    // -------------------------------------------------------------------------

    /** @test */
    public function la_seccion_activa_por_defecto_es_escuela(): void
    {
        $setup = $this->createTutorWithStudentAndFee();

        Livewire::actingAs($setup['tutorUser'])
            ->test(TutorDashboard::class)
            ->assertSet('activeSection', 'escuela');
    }
}
