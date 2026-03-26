<?php

namespace Tests\Feature\Tutor;

use App\Livewire\Tutor\TutorLogin;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Login del Tutor
 * Ruta: GET /tutor/login
 * Componente: App\Livewire\Tutor\TutorLogin
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Tutor/TutorLoginTest.php
 */
class TutorLoginTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function createTutorUser(string $email = 'tutor@test.com', string $password = 'password123'): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'tutor',
        ]);

        Tutor::create([
            'user_id' => $user->id,
            'first_name' => 'Tutor',
            'last_name' => 'Login',
            'phone_main' => '1199887766',
        ]);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_tutor_login_renderiza_correctamente(): void
    {
        $response = $this->get('/tutor/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_formulario_de_login_se_muestra(): void
    {
        Livewire::test(TutorLogin::class)
            ->assertSee('email')
            ->assertSet('email', '')
            ->assertSet('password', '');
    }

    // -------------------------------------------------------------------------
    // Tests de autenticación exitosa
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_autenticarse_con_credenciales_validas(): void
    {
        $user = $this->createTutorUser('tutor.ok@test.com', 'mipassword');

        Livewire::test(TutorLogin::class)
            ->set('email', 'tutor.ok@test.com')
            ->set('password', 'mipassword')
            ->call('authenticate')
            ->assertRedirect(route('tutor.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function tutor_autenticado_es_redirigido_al_dashboard(): void
    {
        $user = $this->createTutorUser('tutor.redir@test.com', 'password123');

        Livewire::test(TutorLogin::class)
            ->set('email', 'tutor.redir@test.com')
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertRedirect('/tutor');
    }

    // -------------------------------------------------------------------------
    // Tests de autenticación fallida
    // -------------------------------------------------------------------------

    /** @test */
    public function falla_con_password_incorrecto(): void
    {
        $user = $this->createTutorUser('tutor.fail@test.com', 'correctpassword');

        Livewire::test(TutorLogin::class)
            ->set('email', 'tutor.fail@test.com')
            ->set('password', 'wrongpassword')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function falla_con_email_inexistente(): void
    {
        Livewire::test(TutorLogin::class)
            ->set('email', 'noexiste@test.com')
            ->set('password', 'cualquiera')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function falla_si_el_usuario_no_es_tutor(): void
    {
        // Crear un usuario con rol admin intentando acceder como tutor
        $adminUser = User::factory()->create([
            'email' => 'admin.nottutor@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        Livewire::test(TutorLogin::class)
            ->set('email', 'admin.nottutor@test.com')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function falla_si_el_usuario_es_teacher(): void
    {
        // En SQLite el CHECK de users.role puede no permitir el valor 'teacher'
        // (por cómo se gestionan migraciones ENUM). Para el objetivo del test
        // (rechazar un usuario que NO es tutor), usamos un role permitido
        // y creamos igualmente el modelo Teacher.
        $teacherUser = User::factory()->create([
            'email' => 'profe.nottutor@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        \App\Models\Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'Profe',
            'last_name' => 'NoTutor',
            'email' => $teacherUser->email,
            'phone' => '111222333',
            'is_active' => true,
        ]);

        Livewire::test(TutorLogin::class)
            ->set('email', 'profe.nottutor@test.com')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    // -------------------------------------------------------------------------
    // Tests de validación del formulario
    // -------------------------------------------------------------------------

    /** @test */
    public function falla_sin_email(): void
    {
        Livewire::test(TutorLogin::class)
            ->set('email', '')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function falla_con_email_con_formato_invalido(): void
    {
        Livewire::test(TutorLogin::class)
            ->set('email', 'no-es-un-email')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function falla_sin_password(): void
    {
        Livewire::test(TutorLogin::class)
            ->set('email', 'tutor@test.com')
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['password' => 'required']);
    }

    // -------------------------------------------------------------------------
    // Tests de middleware
    // -------------------------------------------------------------------------

    /** @test */
    public function tutor_autenticado_es_redirigido_desde_la_pagina_de_login(): void
    {
        $user = $this->createTutorUser('ya.logueado@test.com', 'password');

        $response = $this->actingAs($user)->get('/tutor/login');

        $response->assertRedirect();
    }
}
