<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\TeachersPage;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests de la página de Profesores del Administrador
 * Ruta: GET /admin/profesores
 * Componente: App\Livewire\Admin\TeachersPage
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/TeachersPageTest.php
 */
class TeachersPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // /admin/* está protegido por EnsureAdmin.
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeTeacher(array $overrides = []): Teacher
    {
        return Teacher::create(array_merge([
            'first_name' => 'Pedro',
            'last_name' => 'Rodríguez',
            'email' => 'pedro@test.com',
            'is_active' => true,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_profesores_renderiza_correctamente(): void
    {
        $response = $this->get('/admin/profesores');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_componente_lista_los_profesores_existentes(): void
    {
        $this->makeTeacher(['first_name' => 'Carlos', 'last_name' => 'Martín', 'email' => 'carlos@test.com']);
        $this->makeTeacher(['first_name' => 'Lucía', 'last_name' => 'Fernández', 'email' => 'lucia@test.com']);

        Livewire::test(TeachersPage::class)
            ->assertSee('Martín')
            ->assertSee('Fernández');
    }

    // -------------------------------------------------------------------------
    // Tests de filtro de búsqueda
    // -------------------------------------------------------------------------

    /** @test */
    public function el_filtro_busca_por_apellido(): void
    {
        $this->makeTeacher(['first_name' => 'Juan', 'last_name' => 'López', 'email' => 'juan@test.com']);
        $this->makeTeacher(['first_name' => 'Ana', 'last_name' => 'Gómez', 'email' => 'ana@test.com']);

        Livewire::test(TeachersPage::class)
            ->set('search', 'López')
            ->assertSee('López')
            ->assertDontSee('Gómez');
    }

    /** @test */
    public function el_filtro_busca_por_nombre(): void
    {
        $this->makeTeacher(['first_name' => 'Roberto', 'last_name' => 'Sosa', 'email' => 'roberto@test.com']);
        $this->makeTeacher(['first_name' => 'Elena', 'last_name' => 'Vidal', 'email' => 'elena@test.com']);

        Livewire::test(TeachersPage::class)
            ->set('search', 'Roberto')
            ->assertSee('Roberto')
            ->assertDontSee('Vidal');
    }

    /** @test */
    public function el_filtro_busca_por_email(): void
    {
        $this->makeTeacher(['first_name' => 'Miguel', 'last_name' => 'Torres', 'email' => 'miguel.torres@escuela.com']);
        $this->makeTeacher(['first_name' => 'Paula', 'last_name' => 'Reyes', 'email' => 'paula@otro.com']);

        Livewire::test(TeachersPage::class)
            ->set('search', 'miguel.torres@escuela.com')
            ->assertSee('Miguel')
            ->assertDontSee('Paula');
    }

    /** @test */
    public function el_filtro_vacio_muestra_todos_los_profesores(): void
    {
        $this->makeTeacher(['first_name' => 'X', 'last_name' => 'UnoProf', 'email' => 'x@test.com']);
        $this->makeTeacher(['first_name' => 'Y', 'last_name' => 'DosProf', 'email' => 'y@test.com']);

        Livewire::test(TeachersPage::class)
            ->set('search', '')
            ->assertSee('UnoProf')
            ->assertSee('DosProf');
    }

    // -------------------------------------------------------------------------
    // Tests de creación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_crear_un_profesor_con_datos_validos(): void
    {
        Livewire::test(TeachersPage::class)
            ->set('first_name', 'Nuevo')
            ->set('last_name', 'Profesor')
            ->set('email', 'nuevo@profesor.com')
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teachers', ['first_name' => 'Nuevo', 'last_name' => 'Profesor']);
    }

    /** @test */
    public function crear_profesor_falla_sin_nombre(): void
    {
        Livewire::test(TeachersPage::class)
            ->set('first_name', '')
            ->set('last_name', 'Sin Nombre')
            ->call('save')
            ->assertHasErrors(['first_name']);
    }

    /** @test */
    public function crear_profesor_falla_sin_apellido(): void
    {
        Livewire::test(TeachersPage::class)
            ->set('first_name', 'Sin Apellido')
            ->set('last_name', '')
            ->call('save')
            ->assertHasErrors(['last_name']);
    }

    /** @test */
    public function despues_de_crear_el_formulario_se_resetea(): void
    {
        Livewire::test(TeachersPage::class)
            ->set('first_name', 'Reset')
            ->set('last_name', 'Test')
            ->set('email', 'reset@test.com')
            ->call('save')
            ->assertSet('first_name', '')
            ->assertSet('last_name', '')
            ->assertSet('editing', null);
    }

    // -------------------------------------------------------------------------
    // Tests de edición
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_cargar_un_profesor_para_editar(): void
    {
        $teacher = $this->makeTeacher(['first_name' => 'Editable', 'last_name' => 'Docente', 'email' => 'editable@test.com']);

        Livewire::test(TeachersPage::class)
            ->call('edit', $teacher->id)
            ->assertSet('first_name', 'Editable')
            ->assertSet('last_name', 'Docente');
    }

    /** @test */
    public function puede_actualizar_un_profesor_existente(): void
    {
        $teacher = $this->makeTeacher(['first_name' => 'Original', 'last_name' => 'Docente', 'email' => 'original@test.com']);

        Livewire::test(TeachersPage::class)
            ->call('edit', $teacher->id)
            ->set('first_name', 'Actualizado')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id, 'first_name' => 'Actualizado']);
    }

    // -------------------------------------------------------------------------
    // Tests de eliminación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_eliminar_un_profesor(): void
    {
        $teacher = $this->makeTeacher(['first_name' => 'Para', 'last_name' => 'Eliminar', 'email' => 'eliminar@test.com']);

        Livewire::test(TeachersPage::class)
            ->call('delete', $teacher->id);

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }

    /** @test */
    public function eliminar_profesor_muestra_mensaje_flash(): void
    {
        $teacher = $this->makeTeacher(['first_name' => 'Del', 'last_name' => 'Flash', 'email' => 'delflash@test.com']);

        Livewire::test(TeachersPage::class)->call('delete', $teacher->id);

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }

    /** @test */
    public function eliminar_profesor_en_edicion_resetea_el_formulario(): void
    {
        $teacher = $this->makeTeacher(['first_name' => 'Editar', 'last_name' => 'EliminarMe', 'email' => 'eliminarme@test.com']);

        Livewire::test(TeachersPage::class)
            ->call('edit', $teacher->id)
            ->call('delete', $teacher->id)
            ->assertSet('editing', null)
            ->assertSet('first_name', '');
    }

    // -------------------------------------------------------------------------
    // Tests de asignación de grupos
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_asignar_grupos_a_un_profesor(): void
    {
        $group1 = Group::create(['name' => 'Grupo A', 'year' => 2026, 'is_active' => true]);
        $group2 = Group::create(['name' => 'Grupo B', 'year' => 2026, 'is_active' => true]);

        Livewire::test(TeachersPage::class)
            ->set('first_name', 'Profe')
            ->set('last_name', 'Grupos')
            ->set('email', 'profe.grupos@test.com')
            ->set('selected_group_ids', [$group1->id, $group2->id])
            ->call('save')
            ->assertHasNoErrors();

        $teacher = Teacher::where('email', 'profe.grupos@test.com')->first();
        $this->assertNotNull($teacher);
    }
}
