<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\TreasuryPanel;
use App\Models\Fee;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Panel de Tesorería (Revisión de Pagos) del Administrador
 * Ruta: GET /admin/tesoreria
 * Componente: App\Livewire\Admin\TreasuryPanel
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/TreasuryPanelTest.php
 */
class TreasuryPanelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makePaymentSetup(string $period = '2026-03'): array
    {
        $student = Student::create([
            'first_name' => 'Alumno',
            'last_name' => 'Test',
            'dni' => '11223344',
            'birth_date' => '2010-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $group = Group::create(['name' => 'Grupo Tesoreria', 'year' => 2026, 'is_active' => true]);

        $fee = Fee::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => $period,
            'amount' => 5000,
            'paid_amount' => 0,
            'due_date' => '2026-03-31',
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'first_name' => 'Tutor',
            'last_name' => 'Pagador',
            'phone_main' => '1199998888',
        ]);

        $payment = Payment::create([
            'fee_id' => $fee->id,
            'tutor_id' => $tutor->id,
            'amount_reported' => 5000,
            'status' => 'pending_review',
        ]);

        return compact('student', 'group', 'fee', 'tutor', 'payment');
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_tesoreria_renderiza_correctamente(): void
    {
        $response = $this->get('/admin/tesoreria');

        $response->assertStatus(200);
    }

    /** @test */
    public function muestra_los_pagos_pendientes_en_la_pestana_activa(): void
    {
        $setup = $this->makePaymentSetup();

        Livewire::test(TreasuryPanel::class)
            ->assertSet('activeTab', 'pending')
            ->assertSee('Alumno');
    }

    /** @test */
    public function puede_cambiar_a_la_pestana_de_historial(): void
    {
        Livewire::test(TreasuryPanel::class)
            ->call('setTab', 'history')
            ->assertSet('activeTab', 'history');
    }

    // -------------------------------------------------------------------------
    // Tests de aprobación de pagos
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_aprobar_un_pago_pendiente(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $setup = $this->makePaymentSetup();

        Livewire::test(TreasuryPanel::class)
            ->call('approvePayment', $setup['payment']->id)
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', [
            'id' => $setup['payment']->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('fees', [
            'id' => $setup['fee']->id,
            'status' => 'paid',
        ]);
    }

    /** @test */
    public function aprobar_pago_actualiza_el_campo_reviewed_by(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $setup = $this->makePaymentSetup();

        Livewire::test(TreasuryPanel::class)
            ->call('approvePayment', $setup['payment']->id);

        $this->assertDatabaseHas('payments', [
            'id' => $setup['payment']->id,
            'reviewed_by' => $admin->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Tests de rechazo de pagos
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_rechazar_un_pago_pendiente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $setup = $this->makePaymentSetup();

        Livewire::test(TreasuryPanel::class)
            ->call('rejectPayment', $setup['payment']->id)
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', [
            'id' => $setup['payment']->id,
            'status' => 'rejected',
        ]);
    }

    // -------------------------------------------------------------------------
    // Tests de reversión de pagos
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_volver_un_pago_aprobado_a_revision(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $setup = $this->makePaymentSetup();

        // Primero aprobar el pago
        $setup['payment']->update(['status' => 'approved', 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);
        $setup['fee']->update(['status' => 'paid', 'paid_at' => now()]);

        Livewire::test(TreasuryPanel::class)
            ->call('resetToPending', $setup['payment']->id)
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', [
            'id' => $setup['payment']->id,
            'status' => 'pending_review',
        ]);

        $this->assertDatabaseHas('fees', [
            'id' => $setup['fee']->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function puede_volver_un_pago_rechazado_a_revision(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $setup = $this->makePaymentSetup();

        $setup['payment']->update(['status' => 'rejected', 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);

        Livewire::test(TreasuryPanel::class)
            ->call('resetToPending', $setup['payment']->id)
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', [
            'id' => $setup['payment']->id,
            'status' => 'pending_review',
        ]);
    }

    // -------------------------------------------------------------------------
    // Tests de filtros
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_filtrar_pagos_por_anio(): void
    {
        $setup2026 = $this->makePaymentSetup('2026-03');

        // Crear pago de otro año con alumno diferente
        $student2 = Student::create([
            'first_name' => 'Otro',
            'last_name' => 'Alumno',
            'dni' => '99887766',
            'birth_date' => '2010-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);
        $group2 = Group::create(['name' => 'Grupo 2025', 'year' => 2025, 'is_active' => true]);
        $fee2 = Fee::create([
            'student_id' => $student2->id,
            'group_id' => $group2->id,
            'period' => '2025-03',
            'amount' => 4000,
            'paid_amount' => 0,
            'due_date' => '2025-03-31',
            'status' => 'pending',
            'issued_at' => now(),
        ]);
        $tutorUser2 = User::factory()->create(['role' => 'tutor']);
        $tutor2 = Tutor::create([
            'user_id' => $tutorUser2->id,
            'first_name' => 'Tutor',
            'last_name' => 'Dos',
            'phone_main' => '1100001111',
        ]);
        Payment::create([
            'fee_id' => $fee2->id,
            'tutor_id' => $tutor2->id,
            'amount_reported' => 4000,
            'status' => 'pending_review',
        ]);

        Livewire::test(TreasuryPanel::class)
            ->set('filter_year', 2026)
            ->assertSee('Alumno');
    }

    /** @test */
    public function puede_limpiar_filtros(): void
    {
        Livewire::test(TreasuryPanel::class)
            ->set('filter_year', 2026)
            ->set('filter_month', 3)
            ->set('filter_group_id', 1)
            ->call('clearFilters')
            ->assertSet('filter_year', null)
            ->assertSet('filter_month', null)
            ->assertSet('filter_group_id', null);
    }

    /** @test */
    public function tab_invalido_no_cambia_la_pestana_activa(): void
    {
        Livewire::test(TreasuryPanel::class)
            ->call('setTab', 'invalid_tab')
            ->assertSet('activeTab', 'pending');
    }
}
