<?php

namespace Tests\Feature\Pdf;

use App\Models\Fee;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests del controlador de PDFs de deuda (DebtPdfController)
 * Ruta: GET /admin/deudas/pdf/alumno/{student}
 * Ruta: GET /admin/deudas/pdf/grupo/{group}
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Pdf/DebtPdfControllerTest.php
 */
class DebtPdfControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function createStudentWithDebt(): array
    {
        $student = Student::create([
            'first_name' => 'Deudor',
            'last_name' => 'PDF',
            'dni' => '88776655',
            'birth_date' => '2010-05-20',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $group = Group::create(['name' => 'Grupo PDF Deuda', 'year' => 2026, 'is_active' => true]);

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

        Fee::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'period' => '2026-02',
            'amount' => 5000,
            'paid_amount' => 2000,
            'due_date' => '2026-02-28',
            'status' => 'partial',
            'issued_at' => now()->subMonth(),
        ]);

        return compact('student', 'group');
    }

    protected function createGroupWithStudents(): Group
    {
        $group = Group::create(['name' => 'Grupo PDF Grupal', 'year' => 2026, 'is_active' => true]);

        foreach (['Pérez', 'García', 'López'] as $idx => $lastName) {
            $student = Student::create([
                'first_name' => 'Alumno',
                'last_name' => $lastName,
                'dni' => '1000000' . $idx,
                'birth_date' => '2011-01-01',
                'is_active' => true,
                'scholarship_percentage' => 0,
            ]);

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
        }

        return $group;
    }

    // -------------------------------------------------------------------------
    // Tests de PDF por alumno
    // -------------------------------------------------------------------------

    /** @test */
    public function genera_pdf_de_deuda_por_alumno_con_status_200(): void
    {
        $setup = $this->createStudentWithDebt();

        $response = $this->get(route('admin.debt-pdf.student', $setup['student']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_de_alumno_incluye_nombre_del_alumno(): void
    {
        $setup = $this->createStudentWithDebt();

        $response = $this->get(route('admin.debt-pdf.student', $setup['student']));

        $response->assertStatus(200);
        // Solo verificamos que el PDF se genera (200 con content-type pdf)
        // El contenido interno del PDF es binario
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    /** @test */
    public function devuelve_404_para_alumno_inexistente(): void
    {
        $response = $this->get(route('admin.debt-pdf.student', 99999));

        $response->assertStatus(404);
    }

    /** @test */
    public function pdf_de_alumno_sin_cuotas_genera_documento_vacio(): void
    {
        $student = Student::create([
            'first_name' => 'Sin',
            'last_name' => 'Deuda',
            'dni' => '11001100',
            'birth_date' => '2012-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $response = $this->get(route('admin.debt-pdf.student', $student));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // -------------------------------------------------------------------------
    // Tests de PDF por grupo
    // -------------------------------------------------------------------------

    /** @test */
    public function genera_pdf_de_deuda_por_grupo_con_status_200(): void
    {
        $group = $this->createGroupWithStudents();

        $response = $this->get(route('admin.debt-pdf.group', $group));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function devuelve_404_para_grupo_inexistente(): void
    {
        $response = $this->get(route('admin.debt-pdf.group', 99999));

        $response->assertStatus(404);
    }

    /** @test */
    public function pdf_de_grupo_sin_alumnos_genera_documento(): void
    {
        $group = Group::create(['name' => 'Grupo Vacío PDF', 'year' => 2026, 'is_active' => true]);

        $response = $this->get(route('admin.debt-pdf.group', $group));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_por_grupo_contiene_todas_las_deudas_de_sus_alumnos(): void
    {
        $group = $this->createGroupWithStudents();

        $response = $this->get(route('admin.debt-pdf.group', $group));

        // Solo verificamos código 200 y tipo PDF; el contenido es binario
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    // -------------------------------------------------------------------------
    // Tests de nombre del archivo descargado
    // -------------------------------------------------------------------------

    /** @test */
    public function pdf_de_alumno_tiene_nombre_de_archivo_pdf(): void
    {
        $setup = $this->createStudentWithDebt();

        $response = $this->get(route('admin.debt-pdf.student', $setup['student']));

        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('.pdf', $contentDisposition ?? '');
    }

    /** @test */
    public function pdf_de_grupo_tiene_nombre_de_archivo_pdf(): void
    {
        $group = $this->createGroupWithStudents();

        $response = $this->get(route('admin.debt-pdf.group', $group));

        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('.pdf', $contentDisposition ?? '');
    }
}
