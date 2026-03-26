<?php

namespace App\Livewire\Tutor;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TutorChangePassword extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    #[Layout('layouts.tutor')]
    public function render()
    {
        return view('livewire.tutor.tutor-change-password');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'tutor' || ! $user->tutor) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            $this->redirectRoute('tutor.login');
            return;
        }

        $this->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'new_password.required' => 'La nueva contraseña es obligatoria.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'new_password.confirmed' => 'La confirmación no coincide.',
        ]);

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'La contraseña actual no es correcta.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
            'must_change_password' => false,
            'last_login_at' => now(),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        session()->flash('status', 'Contraseña actualizada correctamente.');

        $this->redirectRoute('tutor.dashboard');
    }
}

