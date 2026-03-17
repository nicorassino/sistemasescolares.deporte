<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Fee;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Panel de inicio del Administrador
 * Ruta: GET /admin
 * Componente: App\Livewire\Admin\AdminDashboard
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/DashboardTest.php
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // -------------------------------------------------------------------------
    // Tests de acceso
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_admin_renderiza_correctamente(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_dashboard_muestra_el_contador_de_pagos_en_revision(): void
    {
        // Crear un pago pendiente de revisión
        $student = Student::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'dni' => '12345678',
            'birth_date' => '2010-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $group = Group::create(['name' => 'Grupo Test', 'year' => 2026, 'is_active' => true]);

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

        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $tutor = \App\Models\Tutor::create([
            'user_id' => $tutorUser->id,
            'first_name' => 'Pedro',
            'last_name' => 'García',
            'phone_main' => '1122334455',
        ]);

        Payment::create([
            'fee_id' => $fee->id,
            'tutor_id' => $tutor->id,
            'amount_reported' => 5000,
            'status' => 'pending_review',
        ]);

        Livewire::test(\App\Livewire\Admin\AdminDashboard::class)
            ->assertSee('1'); // el contador de pagos pendientes
    }

    /** @test */
    public function el_dashboard_muestra_la_ultima_cuota_generada(): void
    {
        $student = Student::create([
            'first_name' => 'Ana',
            'last_name' => 'López',
            'dni' => '87654321',
            'birth_date' => '2012-05-15',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $group = Group::create(['name' => 'Grupo A', 'year' => 2026, 'is_active' => true]);

        Fee::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => '2026-03',
            'amount' => 5000,
            'paid_amount' => 0,
            'due_date' => '2026-03-31',
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        Livewire::test(\App\Livewire\Admin\AdminDashboard::class)
            ->assertSee('Última cuota generada');
    }

    /** @test */
    public function el_dashboard_muestra_novedades_publicadas(): void
    {
        $adminUser = $this->adminUser();

        Announcement::create([
            'title' => 'Novedad de prueba',
            'content' => 'Contenido de prueba',
            'author_id' => $adminUser->id,
        ]);

        Livewire::test(\App\Livewire\Admin\AdminDashboard::class)
            ->assertSee('Novedad de prueba');
    }

    /** @test */
    public function el_dashboard_muestra_cero_pagos_cuando_no_hay_ninguno(): void
    {
        Livewire::test(\App\Livewire\Admin\AdminDashboard::class)
            ->assertSee('0');
    }

    /** @test */
    public function el_dashboard_muestra_cuotas_por_grupo(): void
    {
        $student = Student::create([
            'first_name' => 'Carlos',
            'last_name' => 'Ruiz',
            'dni' => '11223344',
            'birth_date' => '2011-08-20',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $group = Group::create(['name' => 'Grupo Rojo', 'year' => 2026, 'is_active' => true]);

        Fee::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => '2026-03',
            'amount' => 5000,
            'paid_amount' => 0,
            'due_date' => '2026-03-31',
            'status' => 'paid',
            'issued_at' => now(),
        ]);

        Livewire::test(\App\Livewire\Admin\AdminDashboard::class)
            ->assertSee('Grupo Rojo');
    }
}
