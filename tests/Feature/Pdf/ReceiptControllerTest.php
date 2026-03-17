<?php

namespace Tests\Feature\Pdf;

use App\Models\Fee;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Tests del controlador de recibos (ReceiptController)
 * Ruta: GET /recibo/{fee}          (requiere auth)
 * Ruta: GET /recibo/{fee}/descargar (requiere firma URL)
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Pdf/ReceiptControllerTest.php
 */
class ReceiptControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function createFeeWithTutor(): array
    {
        $student = Student::create([
            'first_name' => 'Alumno',
            'last_name' => 'Recibo',
            'dni' => '12312312',
            'birth_date' => '2011-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $group = Group::create(['name' => 'Grupo Recibo', 'year' => 2026, 'is_active' => true]);

        $fee = Fee::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => '2026-03',
            'amount' => 5000,
            'paid_amount' => 5000,
            'due_date' => '2026-03-31',
            'status' => 'paid',
            'issued_at' => now(),
            'paid_at' => now(),
        ]);

        $tutorUser = User::factory()->create([
            'email' => 'tutor.recibo@test.com',
            'password' => Hash::make('password'),
            'role' => 'tutor',
        ]);
        $tutor = Tutor::create([
            'user_id' => $tutorUser->id,
            'first_name' => 'Tutor',
            'last_name' => 'Recibo',
            'phone_main' => '1199887766',
        ]);
        $student->tutors()->attach($tutor->id, ['is_primary' => true, 'relationship_type' => 'parent']);

        Payment::create([
            'fee_id' => $fee->id,
            'tutor_id' => $tutor->id,
            'amount_reported' => 5000,
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        return compact('student', 'group', 'fee', 'tutorUser', 'tutor');
    }

    // -------------------------------------------------------------------------
    // Tests de acceso autenticado (tutor dueño)
    // -------------------------------------------------------------------------

    /** @test */
    public function tutor_dueno_puede_descargar_el_recibo(): void
    {
        $setup = $this->createFeeWithTutor();

        $response = $this->actingAs($setup['tutorUser'])
            ->get(route('receipt.download', $setup['fee']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function admin_puede_descargar_cualquier_recibo(): void
    {
        $setup = $this->createFeeWithTutor();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('receipt.download', $setup['fee']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function tutor_no_dueno_recibe_403(): void
    {
        $setup = $this->createFeeWithTutor();

        // Crear otro tutor que no está relacionado con el alumno
        $otherTutorUser = User::factory()->create([
            'email' => 'otro.tutor@test.com',
            'password' => Hash::make('password'),
            'role' => 'tutor',
        ]);
        Tutor::create([
            'user_id' => $otherTutorUser->id,
            'first_name' => 'Otro',
            'last_name' => 'Tutor',
            'phone_main' => '1100112233',
        ]);

        $response = $this->actingAs($otherTutorUser)
            ->get(route('receipt.download', $setup['fee']));

        $response->assertStatus(403);
    }

    /** @test */
    public function usuario_no_autenticado_no_puede_descargar_recibo(): void
    {
        $setup = $this->createFeeWithTutor();

        $response = $this->get(route('receipt.download', $setup['fee']));

        // Debe redirigir al login
        $response->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // Tests de acceso con URL firmada
    // -------------------------------------------------------------------------

    /** @test */
    public function url_firmada_valida_permite_descarga_sin_autenticacion(): void
    {
        $setup = $this->createFeeWithTutor();

        $signedUrl = URL::temporarySignedRoute(
            'receipt.download.signed',
            now()->addMinutes(30),
            ['fee' => $setup['fee']->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function url_firmada_expirada_da_error(): void
    {
        $setup = $this->createFeeWithTutor();

        // Generar URL que ya expiró
        $signedUrl = URL::temporarySignedRoute(
            'receipt.download.signed',
            now()->subMinute(),
            ['fee' => $setup['fee']->id]
        );

        $response = $this->get($signedUrl);

        // Laravel devuelve 403 o 401 para URLs firmadas inválidas
        $response->assertStatus(403);
    }
}
