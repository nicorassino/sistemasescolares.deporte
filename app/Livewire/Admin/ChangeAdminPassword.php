<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ChangeAdminPassword extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.change-admin-password');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirectRoute('admin.login');
            return;
        }

        $this->validate([
            'current_password' => ['required', 'string', 'min:1'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Ingresá tu contraseña actual.',
            'new_password.required' => 'Ingresá una nueva contraseña.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'new_password.confirmed' => 'Las contraseñas nuevas no coinciden.',
        ]);

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'La contraseña actual no es correcta.');
            return;
        }

        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        session()->flash('status', 'Contraseña actualizada correctamente.');
    }
}

