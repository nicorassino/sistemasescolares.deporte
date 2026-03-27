<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GroupsPage extends Component
{
    public string $name = '';
    public ?string $description = null;
    public ?int $year = null;
    public ?string $level = null;
    public ?int $max_capacity = null;
    public bool $is_active = true;

    public ?Group $editing = null;

    public function mount(): void
    {
        $this->year = (int) date('Y');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.groups-page', [
            'groups' => Group::with('teacher:id,first_name,last_name')
                ->withCount('students')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->editing = null;
    }

    public function edit($id): void
    {
        $id = (int) $id;
        $group = Group::findOrFail($id);
        $this->editing = $group;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->year = $group->year;
        $this->level = $group->level;
        $this->max_capacity = $group->max_capacity;
        $this->is_active = (bool) $group->is_active;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'level' => ['nullable', 'string', 'max:50'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'year.required' => 'El año es obligatorio.',
            'year.integer' => 'El año debe ser un número.',
            'year.min' => 'El año debe ser 2000 o posterior.',
            'year.max' => 'El año no puede ser posterior a 2100.',
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
            session()->flash('message', 'Grupo actualizado correctamente.');
        } else {
            Group::create($validated);
            session()->flash('message', 'Grupo creado correctamente.');
        }

        $this->resetValidation();
        $this->resetForm();
        $this->editing = null;
    }

    public function delete(int $id)
    {
        $group = Group::withCount('students')->findOrFail($id);

        if ($group->students_count > 0 || $group->teacher_id !== null) {
            session()->flash('error', 'No se puede eliminar el grupo porque tiene alumnos o un profesor asignado.');
            return;
        }

        $group->delete();
        if ($this->editing && $this->editing->id === $id) {
            $this->resetForm();
            $this->editing = null;
        }
        session()->flash('message', 'Grupo eliminado.');
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->description = null;
        $this->year = (int) date('Y');
        $this->level = null;
        $this->max_capacity = null;
        $this->is_active = true;
    }
}

