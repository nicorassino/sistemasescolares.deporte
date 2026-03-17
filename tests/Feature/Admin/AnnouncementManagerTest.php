<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AnnouncementManager;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests de la página de Novedades del Administrador
 * Ruta: GET /admin/novedades
 * Componente: App\Livewire\Admin\AnnouncementManager
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Admin/AnnouncementManagerTest.php
 */
class AnnouncementManagerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function makeAnnouncement(int $authorId, array $overrides = []): Announcement
    {
        return Announcement::create(array_merge([
            'title' => 'Novedad de prueba',
            'content' => 'Contenido de prueba.',
            'author_id' => $authorId,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_novedades_renderiza_correctamente(): void
    {
        $response = $this->get('/admin/novedades');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_componente_lista_las_novedades_existentes(): void
    {
        $admin = $this->adminUser();
        $this->makeAnnouncement($admin->id, ['title' => 'Título Primera Novedad']);
        $this->makeAnnouncement($admin->id, ['title' => 'Título Segunda Novedad']);

        Livewire::test(AnnouncementManager::class)
            ->assertSee('Título Primera Novedad')
            ->assertSee('Título Segunda Novedad');
    }

    // -------------------------------------------------------------------------
    // Tests de creación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_crear_una_novedad_autenticado_como_admin(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        Livewire::test(AnnouncementManager::class)
            ->set('title', 'Nueva Novedad')
            ->set('content', 'Contenido de la nueva novedad.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('announcements', ['title' => 'Nueva Novedad']);
    }

    /** @test */
    public function puede_crear_novedad_sin_estar_autenticado_gracias_al_fallback(): void
    {
        // Existe al menos un usuario en la DB para el fallback
        $admin = $this->adminUser();

        Livewire::test(AnnouncementManager::class)
            ->set('title', 'Novedad Sin Auth')
            ->set('content', 'Contenido sin auth.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('announcements', ['title' => 'Novedad Sin Auth']);
    }

    /** @test */
    public function crear_novedad_falla_sin_titulo(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        Livewire::test(AnnouncementManager::class)
            ->set('title', '')
            ->set('content', 'Contenido válido.')
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    }

    /** @test */
    public function crear_novedad_falla_sin_contenido(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        Livewire::test(AnnouncementManager::class)
            ->set('title', 'Título válido')
            ->set('content', '')
            ->call('save')
            ->assertHasErrors(['content' => 'required']);
    }

    /** @test */
    public function crear_novedad_con_imagen_la_almacena(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $image = UploadedFile::fake()->image('foto.jpg', 100, 100);

        Livewire::test(AnnouncementManager::class)
            ->set('title', 'Con Imagen')
            ->set('content', 'Descripción.')
            ->set('image', $image)
            ->call('save')
            ->assertHasNoErrors();

        $announcement = Announcement::where('title', 'Con Imagen')->first();
        $this->assertNotNull($announcement->image_path);
    }

    /** @test */
    public function despues_de_crear_el_formulario_se_resetea(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        Livewire::test(AnnouncementManager::class)
            ->set('title', 'Para Resetear')
            ->set('content', 'Contenido.')
            ->call('save')
            ->assertSet('title', '')
            ->assertSet('content', '')
            ->assertSet('editing', null);
    }

    // -------------------------------------------------------------------------
    // Tests de edición
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_cargar_una_novedad_para_editar(): void
    {
        $admin = $this->adminUser();
        $announcement = $this->makeAnnouncement($admin->id, ['title' => 'Editable', 'content' => 'Texto original.']);

        Livewire::test(AnnouncementManager::class)
            ->call('edit', $announcement->id)
            ->assertSet('title', 'Editable')
            ->assertSet('content', 'Texto original.');
    }

    /** @test */
    public function puede_actualizar_una_novedad_existente(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);
        $announcement = $this->makeAnnouncement($admin->id, ['title' => 'Original', 'content' => 'Viejo contenido.']);

        Livewire::test(AnnouncementManager::class)
            ->call('edit', $announcement->id)
            ->set('title', 'Modificado')
            ->set('content', 'Nuevo contenido.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'title' => 'Modificado']);
    }

    // -------------------------------------------------------------------------
    // Tests de eliminación
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_eliminar_una_novedad(): void
    {
        $admin = $this->adminUser();
        $announcement = $this->makeAnnouncement($admin->id, ['title' => 'Para Borrar']);

        Livewire::test(AnnouncementManager::class)
            ->call('delete', $announcement->id);

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    /** @test */
    public function eliminar_la_novedad_en_edicion_resetea_el_formulario(): void
    {
        $admin = $this->adminUser();
        $announcement = $this->makeAnnouncement($admin->id, ['title' => 'Edit Y Borrar']);

        Livewire::test(AnnouncementManager::class)
            ->call('edit', $announcement->id)
            ->call('delete', $announcement->id)
            ->assertSet('editing', null)
            ->assertSet('title', '');
    }

    // -------------------------------------------------------------------------
    // Test del tope de 15 novedades
    // -------------------------------------------------------------------------

    /** @test */
    public function al_superar_15_novedades_elimina_la_mas_antigua(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        // Crear 15 novedades
        for ($i = 1; $i <= 15; $i++) {
            $this->makeAnnouncement($admin->id, [
                'title' => "Novedad {$i}",
                'content' => "Contenido {$i}",
                'created_at' => now()->subDays(16 - $i),
            ]);
        }

        $oldestId = Announcement::orderBy('created_at', 'asc')->value('id');

        // Agregar la novedad número 16
        Livewire::test(AnnouncementManager::class)
            ->set('title', 'Novedad 16')
            ->set('content', 'Contenido de la novedad 16.')
            ->call('save')
            ->assertHasNoErrors();

        // La más antigua ya no existe
        $this->assertDatabaseMissing('announcements', ['id' => $oldestId]);
        // Y hay exactamente 15
        $this->assertEquals(15, Announcement::count());
    }
}
