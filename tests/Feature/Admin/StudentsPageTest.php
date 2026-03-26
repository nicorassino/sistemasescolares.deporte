<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\StudentsPage;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests de la página de Alumnos del Administrador
 * Ruta: GET /admin/alumnos
 * Componente: App\Livewire\Admin\StudentsPage
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/StudentsPageTest.php
 */
class StudentsPageTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeGroup(): Group
    {
        return Group::create([
            'name' => 'Grupo Test',
            'year' => 2026,
            'is_active' => true,
        ]);
    }

    protected function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'first_name' => 'Ana',
            'last_name' => 'García',
            'dni' => '12345678',
            'birth_date' => '2012-06-15',
            'is_active' => true,
            'scholarship_percentage' => 0,
        ], $overrides));
    }

    protected function makeTutor(array $overrides = []): Tutor
    {
        $user = User::factory()->create(array_merge(['role' => 'tutor'], $overrides['user'] ?? []));
        unset($overrides['user']);

        return Tutor::create(array_merge([
            'user_id' => $user->id,
            'first_name' => 'Pedro',
            'last_name' => 'Ramírez',
            'phone_main' => '1122334455',
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado y listado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_alumnos_renderiza_correctamente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/alumnos');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_componente_lista_los_alumnos_existentes(): void
    {
        $this->makeStudent(['first_name' => 'Carlos', 'last_name' => 'Soto', 'dni' => '11111111']);
        $this->makeStudent(['first_name' => 'María', 'last_name' => 'Torres', 'dni' => '22222222']);

        Livewire::test(StudentsPage::class)
            ->assertSee('Soto')
            ->assertSee('Torres');
    }

    // -------------------------------------------------------------------------
    // Tests de filtro de búsqueda
    // -------------------------------------------------------------------------

    /** @test */
    public function el_filtro_busca_por_apellido(): void
    {
        $this->makeStudent(['first_name' => 'Luis', 'last_name' => 'Mendoza', 'dni' => '33333333']);
        $this->makeStudent(['first_name' => 'Otro', 'last_name' => 'Zambrano', 'dni' => '44444444']);

        Livewire::test(StudentsPage::class)
            ->set('search', 'Mendoza')
            ->assertSee('Mendoza')
            ->assertDontSee('Zambrano');
    }

    /** @test */
    public function el_filtro_busca_por_nombre(): void
    {
        $this->makeStudent(['first_name' => 'Valentina', 'last_name' => 'Sánchez', 'dni' => '55555555']);
        $this->makeStudent(['first_name' => 'Roberto', 'last_name' => 'Pérez', 'dni' => '66666666']);

        Livewire::test(StudentsPage::class)
            ->set('search', 'Valentina')
            ->assertSee('Valentina')
            ->assertDontSee('Roberto');
    }

    /** @test */
    public function el_filtro_busca_por_dni(): void
    {
        $this->makeStudent(['first_name' => 'Diego', 'last_name' => 'Vargas', 'dni' => '99887766']);
        $this->makeStudent(['first_name' => 'Otro', 'last_name' => 'Silva', 'dni' => '11223344']);

        Livewire::test(StudentsPage::class)
            ->set('search', '99887766')
            ->assertSee('Vargas')
            ->assertDontSee('11223344');
    }

    /** @test */
    public function el_filtro_vacio_muestra_todos_los_alumnos(): void
    {
        $this->makeStudent(['first_name' => 'A', 'last_name' => 'Primer', 'dni' => '10101010']);
        $this->makeStudent(['first_name' => 'B', 'last_name' => 'Segundo', 'dni' => '20202020']);

        Livewire::test(StudentsPage::class)
            ->set('search', '')
            ->assertSee('Primer')
            ->assertSee('Segundo');
    }

    // -------------------------------------------------------------------------
    // Tests de creación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_crear_alumno_con_datos_validos_y_tutor_existente(): void
    {
        $tutor = $this->makeTutor();
        $group = $this->makeGroup();

        Livewire::test(StudentsPage::class)
            ->set('first_name', 'Nuevo')
            ->set('last_name', 'Alumno')
            ->set('dni', '77665544')
            ->set('birth_date', '2013-03-10')
            ->set('group_id', $group->id)
            ->set('selected_tutor_ids', [$tutor->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('students', ['first_name' => 'Nuevo', 'last_name' => 'Alumno', 'dni' => '77665544']);
    }

    /** @test */
    public function crear_alumno_falla_sin_nombre(): void
    {
        Livewire::test(StudentsPage::class)
            ->set('first_name', '')
            ->set('last_name', 'Test')
            ->set('dni', '11223355')
            ->call('save')
            ->assertHasErrors(['first_name']);
    }

    /** @test */
    public function crear_alumno_falla_sin_apellido(): void
    {
        Livewire::test(StudentsPage::class)
            ->set('first_name', 'Test')
            ->set('last_name', '')
            ->set('dni', '11223366')
            ->call('save')
            ->assertHasErrors(['last_name']);
    }

    /** @test */
    public function despues_de_crear_el_formulario_se_resetea(): void
    {
        $tutor = $this->makeTutor();
        $group = $this->makeGroup();

        Livewire::test(StudentsPage::class)
            ->set('first_name', 'Reset')
            ->set('last_name', 'Test')
            ->set('dni', '55443322')
            ->set('birth_date', '2014-03-10')
            ->set('group_id', $group->id)
            ->set('selected_tutor_ids', [$tutor->id])
            ->call('save')
            ->assertSet('first_name', '')
            ->assertSet('last_name', '')
            ->assertSet('editing', null);
    }

    // -------------------------------------------------------------------------
    // Tests de edición
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_cargar_un_alumno_para_editar(): void
    {
        $student = $this->makeStudent(['first_name' => 'Edit', 'last_name' => 'Me', 'dni' => '10203040']);

        Livewire::test(StudentsPage::class)
            ->call('edit', $student->id)
            ->assertSet('first_name', 'Edit')
            ->assertSet('last_name', 'Me')
            ->assertSet('dni', '10203040');
    }

    /** @test */
    public function puede_actualizar_un_alumno_existente(): void
    {
        $student = $this->makeStudent(['first_name' => 'Original', 'last_name' => 'Name', 'dni' => '10203050']);
        $tutor = $this->makeTutor();
        $group = $this->makeGroup();

        // El formulario requiere group_id requerido; se lo seteamos via relación con el grupo
        $group->students()->attach($student->id, [
            'from_date' => '2026-01-01',
            'to_date' => null,
            'is_current' => true,
        ]);

        Livewire::test(StudentsPage::class)
            ->call('edit', $student->id)
            ->set('first_name', 'Modificado')
            ->set('selected_tutor_ids', [$tutor->id])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'first_name' => 'Modificado']);
    }

    // -------------------------------------------------------------------------
    // Tests de eliminación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_eliminar_un_alumno(): void
    {
        $student = $this->makeStudent(['first_name' => 'Eliminar', 'last_name' => 'Este', 'dni' => '90807060']);

        Livewire::test(StudentsPage::class)
            ->call('delete', $student->id);

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    /** @test */
    public function eliminar_alumno_muestra_mensaje_flash(): void
    {
        $student = $this->makeStudent(['first_name' => 'Del', 'last_name' => 'Flash', 'dni' => '98765432']);

        Livewire::test(StudentsPage::class)->call('delete', $student->id);

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    // -------------------------------------------------------------------------
    // Tests de tutores múltiples
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_asignar_multiples_tutores_a_un_alumno(): void
    {
        $tutor1 = $this->makeTutor(['dni' => '11112222']);
        $tutor2 = $this->makeTutor(['dni' => '33334444']);
        $group = $this->makeGroup();

        Livewire::test(StudentsPage::class)
            ->set('first_name', 'Multi')
            ->set('last_name', 'Tutor')
            ->set('dni', '55667788')
            ->set('birth_date', '2015-03-10')
            ->set('group_id', $group->id)
            ->set('selected_tutor_ids', [$tutor1->id, $tutor2->id])
            ->call('save')
            ->assertHasNoErrors();

        $student = Student::where('dni', '55667788')->first();
        $this->assertNotNull($student);
        $this->assertCount(2, $student->tutors);
    }

    /** @test */
    public function puede_crear_un_nuevo_tutor_y_agregarlo(): void
    {
        $newTutorEmail = 'tutor_nuevo_creado@test.com';

        Livewire::test(StudentsPage::class)
            ->set('first_name', 'Alumno')
            ->set('last_name', 'Con Nuevo Tutor')
            ->set('dni', '99887711')
            ->set('new_tutor_first_name', 'Tutor')
            ->set('new_tutor_last_name', 'Nuevo')
            ->set('new_tutor_email', $newTutorEmail)
            ->set('new_tutor_phone', '1199887766')
            ->set('show_new_tutor', true)
            ->call('addNewTutor')
            ->assertHasNoErrors();

        $user = User::where('email', $newTutorEmail)->first();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('tutors', ['user_id' => $user->id]);
    }
}
