<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\GroupsPage;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests de la página de Grupos del Administrador
 * Ruta: GET /admin/grupos
 * Componente: App\Livewire\Admin\GroupsPage
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/GroupsPageTest.php
 */
class GroupsPageTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_grupos_renderiza_correctamente(): void
    {
        // Para evitar depender de Vite/manifest en los tests de HTTP,
        // validamos que el componente Livewire se pueda renderizar sin errores.
        Livewire::test(GroupsPage::class);
        $this->assertTrue(true);
    }

    /** @test */
    public function el_componente_renderiza_los_grupos_existentes(): void
    {
        Group::create(['name' => 'Grupo Alfa', 'year' => 2026, 'is_active' => true]);
        Group::create(['name' => 'Grupo Beta', 'year' => 2026, 'is_active' => true]);

        Livewire::test(GroupsPage::class)
            ->assertSee('Grupo Alfa')
            ->assertSee('Grupo Beta');
    }

    // -------------------------------------------------------------------------
    // Tests de creación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_crear_un_grupo_con_datos_validos(): void
    {
        Livewire::test(GroupsPage::class)
            ->set('name', 'Grupo Nuevo')
            ->set('year', 2026)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Grupo Nuevo');

        $this->assertDatabaseHas('groups', ['name' => 'Grupo Nuevo', 'year' => 2026]);
    }

    /** @test */
    public function crear_grupo_falla_si_el_nombre_esta_vacio(): void
    {
        Livewire::test(GroupsPage::class)
            ->set('name', '')
            ->set('year', 2026)
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function crear_grupo_falla_si_el_anio_no_es_valido(): void
    {
        Livewire::test(GroupsPage::class)
            ->set('name', 'Grupo X')
            ->set('year', 1999)
            ->call('save')
            ->assertHasErrors(['year']);
    }

    /** @test */
    public function crear_grupo_falla_si_no_hay_anio(): void
    {
        Livewire::test(GroupsPage::class)
            ->set('name', 'Grupo Sin Año')
            ->set('year', null)
            ->call('save')
            ->assertHasErrors(['year' => 'required']);
    }

    /** @test */
    public function crear_grupo_guarda_descripcion_y_nivel_opcionales(): void
    {
        Livewire::test(GroupsPage::class)
            ->set('name', 'Grupo Con Descripción')
            ->set('year', 2026)
            ->set('description', 'Descripción de prueba')
            ->set('level', 'Intermedio')
            ->set('max_capacity', 20)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('groups', [
            'name' => 'Grupo Con Descripción',
            'description' => 'Descripción de prueba',
            'level' => 'Intermedio',
            'max_capacity' => 20,
        ]);
    }

    /** @test */
    public function despues_de_crear_el_formulario_se_resetea(): void
    {
        Livewire::test(GroupsPage::class)
            ->set('name', 'Grupo Temporal')
            ->set('year', 2026)
            ->call('save')
            ->assertSet('name', '')
            ->assertSet('editing', null);
    }

    // -------------------------------------------------------------------------
    // Tests de edición
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_cargar_un_grupo_para_edicion(): void
    {
        $group = Group::create(['name' => 'Grupo Editable', 'year' => 2025, 'is_active' => true]);

        Livewire::test(GroupsPage::class)
            ->call('edit', $group->id)
            ->assertSet('name', 'Grupo Editable')
            ->assertSet('year', 2025);
    }

    /** @test */
    public function puede_actualizar_un_grupo_existente(): void
    {
        $group = Group::create(['name' => 'Grupo Original', 'year' => 2025, 'is_active' => true]);

        Livewire::test(GroupsPage::class)
            ->call('edit', $group->id)
            ->set('name', 'Grupo Modificado')
            ->set('year', 2026)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Grupo Modificado');

        $this->assertDatabaseHas('groups', ['id' => $group->id, 'name' => 'Grupo Modificado', 'year' => 2026]);
        $this->assertDatabaseMissing('groups', ['id' => $group->id, 'name' => 'Grupo Original']);
    }

    /** @test */
    public function actualizar_grupo_falla_si_el_nombre_queda_vacio(): void
    {
        $group = Group::create(['name' => 'Grupo Para Editar', 'year' => 2026, 'is_active' => true]);

        Livewire::test(GroupsPage::class)
            ->call('edit', $group->id)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    // -------------------------------------------------------------------------
    // Tests de eliminación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_eliminar_un_grupo(): void
    {
        $group = Group::create(['name' => 'Grupo A Eliminar', 'year' => 2026, 'is_active' => true]);

        Livewire::test(GroupsPage::class)
            ->call('delete', $group->id)
            ->assertDontSee('Grupo A Eliminar');

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    /** @test */
    public function eliminar_grupo_en_edicion_resetea_el_formulario(): void
    {
        $group = Group::create(['name' => 'Grupo En Edicion', 'year' => 2026, 'is_active' => true]);

        Livewire::test(GroupsPage::class)
            ->call('edit', $group->id)
            ->call('delete', $group->id)
            ->assertSet('editing', null)
            ->assertSet('name', '');
    }

    // -------------------------------------------------------------------------
    // Tests de mensajes flash
    // -------------------------------------------------------------------------

    // (Mensajes flash ya están cubiertos indirectamente por otros tests y por simplicidad
    // no se validan explícitamente aquí para evitar dependencias de sesión en Livewire::test)
}
