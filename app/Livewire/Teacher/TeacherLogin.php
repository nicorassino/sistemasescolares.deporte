<?php

namespace App\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TeacherLogin extends Component
{
    public string $email = '';

    public string $password = '';

    #[Layout('layouts.teacher')]
    public function render()
    {
        return view('livewire.teacher.teacher-login');
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

        if ($user->role !== 'teacher' || ! $user->teacher) {
            Auth::logout();
            $this->addError('email', 'Este acceso es solo para profesores.');
            return;
        }

        session()->regenerate();

        return redirect()->route('profesor.dashboard');
    }
}
