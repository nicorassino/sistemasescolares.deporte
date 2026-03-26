<?php

namespace App\Livewire\Admin;

use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ImpersonateTeacher extends Component
{
    public ?int $teacherId = null;

    #[Layout('layouts.app')]
    public function render()
    {
        $teachers = Teacher::query()
            ->whereNotNull('user_id')
            ->where('is_active', true)
            ->whereHas('user', function ($q) {
                $q->where('role', 'teacher')->where('is_active', true);
            })
            ->with(['user'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('livewire.admin.impersonate-teacher', [
            'teachers' => $teachers,
        ]);
    }

    public function enterTeacher(): void
    {
        $this->validate([
            'teacherId' => ['required', 'integer', 'exists:teachers,id'],
        ], [
            'teacherId.required' => 'Elegí un profesor.',
        ]);

        $teacher = Teacher::query()
            ->where('id', $this->teacherId)
            ->whereNotNull('user_id')
            ->where('is_active', true)
            ->whereHas('user', function ($q) {
                $q->where('role', 'teacher')->where('is_active', true);
            })
            ->with('user')
            ->firstOrFail();

        $admin = Auth::user();

        session()->put('impersonator_admin_id', $admin?->id);

        Auth::login($teacher->user);
        session()->regenerate();

        session()->flash('status', 'Accediste al portal como profesor para gestionarlo.');

        $this->redirectRoute('profesor.dashboard');
    }
}

