<?php

namespace App\Livewire\Tutor;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TutorLogin extends Component
{
    public string $email = '';

    public string $password = '';

    #[Layout('layouts.tutor')]
    public function render()
    {
        return view('livewire.tutor.tutor-login');
    }

    public function authenticate()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->addError('email', 'Credenciales incorrectas. Verificá tu correo y contraseña.');
            return;
        }

        $user = Auth::user();

        if ($user->role !== 'tutor' || ! $user->tutor) {
            Auth::logout();
            $this->addError('email', 'Este acceso es solo para padres y tutores.');
            return;
        }

        session()->regenerate();

        if ((bool) $user->must_change_password) {
            return redirect()->route('tutor.change-password');
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        return redirect()->route('tutor.dashboard');
    }
}
