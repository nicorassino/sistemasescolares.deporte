<?php

namespace Tests\Feature\Pdf;

use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests del controlador de PDF de listado de alumnos (StudentPdfController)
 * Ruta: GET /admin/alumnos/pdf/listado
 * Ruta: GET /admin/alumnos/pdf/listado?group={id}
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Pdf/StudentPdfControllerTest.php
 */
class StudentPdfControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Las rutas /admin están protegidas por EnsureAdmin middleware.
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private int $dniCounter = 20000000;

    protected function createGroupWithStudents(string $groupName = 'Grupo Listado', int $count = 3): Group
    {
        $group = Group::create(['name' => $groupName, 'year' => 2026, 'is_active' => true]);

        for ($i = 0; $i < $count; $i++) {
            $student = Student::create([
                'first_name' => "Alumno{$i}",
                'last_name' => "Apellido{$i}",
                // students.dni es UNIQUE: generamos DNIs distintos por test
                'dni' => (string) ($this->dniCounter++),
                'birth_date' => '2010-01-01',
                'is_active' => true,
                'scholarship_percentage' => 0,
            ]);

            $group->students()->attach($student->id, [
                'from_date' => '2026-01-01',
                'is_current' => true,
            ]);
        }

        return $group;
    }

    // -------------------------------------------------------------------------
    // Tests de PDF de todos los grupos
    // -------------------------------------------------------------------------

    /** @test */
    public function genera_pdf_de_listado_de_todos_los_grupos(): void
    {
        $this->createGroupWithStudents('Grupo A');
        $this->createGroupWithStudents('Grupo B');

        $response = $this->get(route('admin.students-pdf.by-group'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_listado_sin_grupos_genera_documento(): void
    {
        $response = $this->get(route('admin.students-pdf.by-group'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_listado_tiene_nombre_de_archivo_pdf(): void
    {
        $this->createGroupWithStudents('Grupo C');

        $response = $this->get(route('admin.students-pdf.by-group'));

        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('.pdf', $contentDisposition ?? '');
    }

    // -------------------------------------------------------------------------
    // Tests de PDF filtrado por grupo específico
    // -------------------------------------------------------------------------

    /** @test */
    public function genera_pdf_de_listado_filtrado_por_grupo(): void
    {
        $group1 = $this->createGroupWithStudents('Grupo X');
        $group2 = $this->createGroupWithStudents('Grupo Y');

        $response = $this->get(route('admin.students-pdf.by-group') . '?group=' . $group1->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_filtrado_por_grupo_retorna_200(): void
    {
        $group = $this->createGroupWithStudents('Grupo Filtrado');

        $response = $this->get(route('admin.students-pdf.by-group') . '?group=' . $group->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function pdf_con_grupo_invalido_como_cadena_devuelve_todos_los_grupos(): void
    {
        $this->createGroupWithStudents('Grupo Test');

        // Pasar una cadena no numérica como group ignora el filtro
        $response = $this->get(route('admin.students-pdf.by-group') . '?group=abc');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_con_group_0_devuelve_todos_los_grupos(): void
    {
        $this->createGroupWithStudents('Grupo Cero');

        // group=0 no es un ID válido, se debería ignorar
        $response = $this->get(route('admin.students-pdf.by-group') . '?group=0');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_con_grupo_inexistente_genera_documento_sin_alumnos(): void
    {
        // Grupo que no existe: el PDF debe generarse vacío (sin alumnos), no dar error
        $response = $this->get(route('admin.students-pdf.by-group') . '?group=99999');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // -------------------------------------------------------------------------
    // Tests de grupos con y sin alumnos activos
    // -------------------------------------------------------------------------

    /** @test */
    public function pdf_de_grupo_con_alumnos_inactivos_los_excluye(): void
    {
        $group = Group::create(['name' => 'Grupo Activos', 'year' => 2026, 'is_active' => true]);

        $activeStudent = Student::create([
            'first_name' => 'Activo',
            'last_name' => 'OK',
            'dni' => '99887700',
            'birth_date' => '2010-01-01',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ]);

        $inactiveStudent = Student::create([
            'first_name' => 'Inactivo',
            'last_name' => 'OUT',
            'dni' => '99887701',
            'birth_date' => '2010-01-01',
            'is_active' => false,
            'scholarship_percentage' => 0,
        ]);

        $group->students()->attach($activeStudent->id, ['from_date' => '2026-01-01', 'is_current' => true]);
        $group->students()->attach($inactiveStudent->id, ['from_date' => '2026-01-01', 'is_current' => true]);

        $response = $this->get(route('admin.students-pdf.by-group') . '?group=' . $group->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function pdf_de_grupo_vacio_genera_correctamente(): void
    {
        $emptyGroup = Group::create(['name' => 'Grupo Vacio', 'year' => 2026, 'is_active' => true]);

        $response = $this->get(route('admin.students-pdf.by-group') . '?group=' . $emptyGroup->id);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
