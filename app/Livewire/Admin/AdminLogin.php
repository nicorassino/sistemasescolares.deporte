<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminLogin extends Component
{
    public string $login_email = '';
    public string $login_password = '';

    #[Layout('layouts.admin-login')]
    public function render()
    {
        return view('livewire.admin.admin-login');
    }

    public function authenticate(): void
    {
        $this->validate([
            'login_email' => ['required', 'string', 'max:190'],
            'login_password' => ['required', 'string', 'min:1'],
        ], [
            'login_email.required' => 'El usuario es obligatorio.',
            'login_password.required' => 'La contraseña es obligatoria.',
        ]);

        $identifier = Str::lower(trim($this->login_email));
        $ip = request()->ip() ?? 'unknown';
        $throttleKey = 'admin-login|' . $identifier . '|' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('login_email', 'Demasiados intentos. Esperá ' . $seconds . ' segundos e intentá de nuevo.');
            return;
        }

        if (! Auth::attempt(['email' => $this->login_email, 'password' => $this->login_password])) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('login_email', 'Credenciales incorrectas. Verificá usuario y contraseña.');
            return;
        }

        $user = Auth::user();

        if (! $user || $user->role !== 'admin' || ! $user->is_active) {
            Auth::logout();
            $this->addError('login_email', 'Acceso denegado.');
            return;
        }

        RateLimiter::clear($throttleKey);

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        session()->regenerate();

        $this->redirectRoute('admin.dashboard');
    }
}

