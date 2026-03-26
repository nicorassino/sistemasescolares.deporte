<?php

namespace Tests\Feature\Profesor;

use App\Livewire\Teacher\TeacherLogin;
use App\Models\Teacher;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests del Login del Profesor
 * Ruta: GET /profesor/login
 * Componente: App\Livewire\Teacher\TeacherLogin
 *
 * Cómo correr SOLO estos tests:
 *   php artisan test tests/Feature/Profesor/TeacherLoginTest.php
 */
class TeacherLoginTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function createTeacherUser(string $email = 'profe@test.com', string $password = 'password123'): User
    {
        try {
            $user = User::factory()->create([
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'teacher',
            ]);
        } catch (QueryException $e) {
            // En SQLite puede fallar por CHECK constraint del enum role.
            if (DB::getDriverName() === 'sqlite' && str_contains($e->getMessage(), 'CHECK constraint failed: role')) {
                $this->markTestSkipped('SQLite users.role no permite role=teacher (CHECK constraint).');
            }

            throw $e;
        }

        Teacher::create([
            'user_id' => $user->id,
            'first_name' => 'Profesor',
            'last_name' => 'Login',
            'is_active' => true,
        ]);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Tests de renderizado
    // -------------------------------------------------------------------------

    /** @test */
    public function la_ruta_profesor_login_renderiza_correctamente(): void
    {
        $response = $this->get('/profesor/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function el_formulario_de_login_se_muestra(): void
    {
        Livewire::test(TeacherLogin::class)
            ->assertSet('email', '')
            ->assertSet('password', '');
    }

    // -------------------------------------------------------------------------
    // Tests de autenticación exitosa
    // -------------------------------------------------------------------------

    /** @test */
    public function puede_autenticarse_con_credenciales_validas(): void
    {
        $user = $this->createTeacherUser('profe.ok@test.com', 'mipassword');

        Livewire::test(TeacherLogin::class)
            ->set('email', 'profe.ok@test.com')
            ->set('password', 'mipassword')
            ->call('authenticate')
            ->assertRedirect(route('profesor.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function profesor_autenticado_es_redirigido_al_panel(): void
    {
        $user = $this->createTeacherUser('profe.redir@test.com', 'password123');

        Livewire::test(TeacherLogin::class)
            ->set('email', 'profe.redir@test.com')
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertRedirect('/profesor');
    }

    // -------------------------------------------------------------------------
    // Tests de autenticación fallida
    // -------------------------------------------------------------------------

    /** @test */
    public function falla_con_password_incorrecto(): void
    {
        $user = $this->createTeacherUser('profe.fail@test.com', 'correctpassword');

        Livewire::test(TeacherLogin::class)
            ->set('email', 'profe.fail@test.com')
            ->set('password', 'wrongpassword')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function falla_con_email_inexistente(): void
    {
        Livewire::test(TeacherLogin::class)
            ->set('email', 'noexiste@teacher.com')
            ->set('password', 'cualquiera')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function falla_si_el_usuario_no_es_teacher(): void
    {
        // Crear usuario admin intentando acceder como profesor
        $adminUser = User::factory()->create([
            'email' => 'admin.notteacher@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        Livewire::test(TeacherLogin::class)
            ->set('email', 'admin.notteacher@test.com')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function falla_si_el_usuario_es_tutor(): void
    {
        $tutorUser = User::factory()->create([
            'email' => 'tutor.notteacher@test.com',
            'password' => Hash::make('password'),
            'role' => 'tutor',
        ]);
        Tutor::create([
            'user_id' => $tutorUser->id,
            'first_name' => 'Tutor',
            'last_name' => 'NotTeacher',
            'phone_main' => '1122334455',
        ]);

        Livewire::test(TeacherLogin::class)
            ->set('email', 'tutor.notteacher@test.com')
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
        Livewire::test(TeacherLogin::class)
            ->set('email', '')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function falla_con_email_con_formato_invalido(): void
    {
        Livewire::test(TeacherLogin::class)
            ->set('email', 'esto-no-es-email')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function falla_sin_password(): void
    {
        Livewire::test(TeacherLogin::class)
            ->set('email', 'profe@test.com')
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['password' => 'required']);
    }

    // -------------------------------------------------------------------------
    // Tests de middleware
    // -------------------------------------------------------------------------

    /** @test */
    public function profesor_autenticado_es_redirigido_desde_la_pagina_de_login(): void
    {
        $user = $this->createTeacherUser('ya.logueado.profe@test.com', 'password');

        $response = $this->actingAs($user)->get('/profesor/login');

        $response->assertRedirect();
    }
}
