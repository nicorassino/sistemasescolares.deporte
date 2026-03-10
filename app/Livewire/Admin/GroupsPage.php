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

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.groups-page', [
            'groups' => Group::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->editing = null;
    }

    public function edit(int $id)
    {
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
            'year' => ['nullable', 'integer'],
            'level' => ['nullable', 'string', 'max:50'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
        } else {
            Group::create($validated);
        }

        $this->resetForm();
        $this->editing = null;
    }

    public function delete(int $id)
    {
        Group::findOrFail($id)->delete();
        if ($this->editing && $this->editing->id === $id) {
            $this->resetForm();
            $this->editing = null;
        }
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->description = null;
        $this->year = null;
        $this->level = null;
        $this->max_capacity = null;
        $this->is_active = true;
    }
}

