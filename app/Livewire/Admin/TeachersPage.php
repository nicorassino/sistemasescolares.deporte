<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TeachersPage extends Component
{
    public string $search = '';

    public string $first_name = '';
    public string $last_name = '';
    public ?string $email = null;
    public ?string $phone = null;
    public bool $is_active = true;
    public ?string $notes = null;

    /** Email para ingresar al panel del profesor (crea/actualiza User) */
    public ?string $login_email = null;
    /** Contraseña para el panel (solo al crear acceso o al cambiar) */
    public string $login_password = '';

    /**
     * @var array<int>
     */
    public array $selected_group_ids = [];

    public ?Teacher $editing = null;

    #[Layout('layouts.app')]
    public function render()
    {
        $teachersQuery = Teacher::with(['groups', 'user'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        if (trim($this->search) !== '') {
            $q = trim($this->search);
            $teachersQuery->where(function ($query) use ($q) {
                $query->where('last_name', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return view('livewire.admin.teachers-page', [
            'teachers' => $teachersQuery->get(),
            'groups' => Group::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editing = null;
    }

    public function edit(int $id): void
    {
        $teacher = Teacher::with(['groups', 'user'])->findOrFail($id);

        $this->editing = $teacher;
        $this->first_name = $teacher->first_name;
        $this->last_name = $teacher->last_name;
        $this->email = $teacher->email;
        $this->phone = $teacher->phone;
        $this->is_active = (bool) $teacher->is_active;
        $this->notes = $teacher->notes;
        $this->selected_group_ids = $teacher->groups->pluck('id')->all();
        $this->login_email = $teacher->user?->email;
        $this->login_password = '';
    }

    public function save(): void
    {
        $userId = $this->editing?->user_id;
        $loginEmailRules = ['nullable', 'email', 'max:190'];
        if (trim((string) $this->login_email) !== '') {
            $loginEmailRules[] = $userId
                ? Rule::unique('users', 'email')->ignore($userId)
                : Rule::unique('users', 'email');
        }

        $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'selected_group_ids' => ['array'],
            'selected_group_ids.*' => [
                'integer',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'login_email' => $loginEmailRules,
            'login_password' => [
                'nullable',
                'string',
                'min:6',
                function (string $attr, $value, \Closure $fail) {
                    if (trim((string) $this->login_email) !== '' && ! $this->editing?->user_id && trim((string) $value) === '') {
                        $fail('Al dar acceso al panel, la contraseña es obligatoria (mín. 6 caracteres).');
                    }
                },
            ],
        ], [
            'login_email.unique' => 'Ese correo ya está usado por otro usuario del sistema.',
            'login_password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $teacherData = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];

        if ($this->editing) {
            $this->editing->update($teacherData);
            $teacher = $this->editing;
        } else {
            $teacher = Teacher::create($teacherData);
        }

        // Grupos a cargo
        Group::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);
        if (! empty($this->selected_group_ids)) {
            Group::whereIn('id', $this->selected_group_ids)
                ->where('is_active', true)
                ->update(['teacher_id' => $teacher->id]);
        }

        // Acceso al panel: crear o actualizar User
        $loginEmail = trim((string) $this->login_email);
        if ($loginEmail !== '') {
            $password = trim($this->login_password) !== ''
                ? Hash::make($this->login_password)
                : null;

            if ($teacher->user_id) {
                $user = $teacher->user;
                $user->email = $loginEmail;
                if ($password !== null) {
                    $user->password = $password;
                }
                $user->name = trim($this->first_name . ' ' . $this->last_name);
                $user->role = 'teacher';
                $user->is_active = $this->is_active;
                $user->save();
            } else {
                $user = User::create([
                    'name' => trim($this->first_name . ' ' . $this->last_name),
                    'email' => $loginEmail,
                    'password' => $password ?? Hash::make('pasepase'),
                    'role' => 'teacher',
                    'is_active' => $this->is_active,
                ]);
                $teacher->update(['user_id' => $user->id]);
            }
        }

        $this->resetForm();
        $this->editing = null;
    }

    public function delete(int $id): void
    {
        $teacher = Teacher::findOrFail($id);

        Group::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);
        if ($teacher->user_id) {
            User::where('id', $teacher->user_id)->delete();
        }

        $teacher->delete();

        if ($this->editing && $this->editing->id === $id) {
            $this->resetForm();
            $this->editing = null;
        }
    }

    protected function resetForm(): void
    {
        $this->first_name = '';
        $this->last_name = '';
        $this->email = null;
        $this->phone = null;
        $this->is_active = true;
        $this->notes = null;
        $this->selected_group_ids = [];
        $this->login_email = null;
        $this->login_password = '';
    }
}

