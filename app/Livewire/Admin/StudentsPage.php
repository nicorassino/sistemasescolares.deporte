<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class StudentsPage extends Component
{
    public string $search = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $dni = '';
    public string $birth_date = '';
    public ?string $gender = null;
    public bool $is_active = true;
    public int $scholarship_percentage = 0;
    public ?int $group_id = null;

    public string $tutor_search = '';
    /** @var array<int> */
    public array $selected_tutor_ids = [];

    public bool $show_new_tutor = false;
    public string $new_tutor_first_name = '';
    public string $new_tutor_last_name = '';
    public string $new_tutor_email = '';
    public string $new_tutor_phone = '';
    public string $new_tutor_dni = '';

    public ?Tutor $editingTutor = null;
    public string $editing_tutor_first_name = '';
    public string $editing_tutor_last_name = '';
    public string $editing_tutor_email = '';
    public string $editing_tutor_phone = '';
    public string $editing_tutor_dni = '';

    public ?Student $editing = null;

    /** Grupo seleccionado para el PDF (null = todos). */
    public ?int $pdf_group_id = null;

    #[Layout('layouts.app')]
    public function render()
    {
        $studentsQuery = Student::with(['tutors', 'groups'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        if (trim($this->search) !== '') {
            $q = trim($this->search);
            $studentsQuery->where(function ($query) use ($q) {
                $query
                    ->where('last_name', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%");
            });
        }

        return view('livewire.admin.students-page', [
            'students' => $studentsQuery->get(),
            'groups' => Group::orderBy('name')->get(),
            'tutorResults' => $this->tutorResults(),
            'selectedTutors' => count($this->selected_tutor_ids)
                ? Tutor::with('user:id,email')->whereIn('id', $this->selected_tutor_ids)->get()
                : collect(),
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->editing = null;
        $this->resetTutorSelection();
    }

    public function edit(int $id)
    {
        $student = Student::with(['tutors', 'groups'])->findOrFail($id);
        $this->editing = $student;
        $this->first_name = $student->first_name;
        $this->last_name = $student->last_name;
        $this->dni = $student->dni;
        $this->birth_date = $student->birth_date ? date('Y-m-d', strtotime((string) $student->birth_date)) : '';
        $this->gender = $student->gender;
        $this->is_active = (bool) $student->is_active;
        $this->scholarship_percentage = (int) ($student->scholarship_percentage ?? 0);
        $this->group_id = $student->groups->first()->id ?? null;
        $this->selected_tutor_ids = $student->tutors->pluck('id')->all();
        $this->show_new_tutor = false;
        $this->resetNewTutor();
    }

    public function save()
    {
        $dniRule = Rule::unique('students', 'dni');
        if ($this->editing) {
            $dniRule = $dniRule->ignore($this->editing->id);
        }

        $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'max:20', $dniRule],
            'birth_date' => ['required', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'is_active' => ['boolean'],
            'scholarship_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'group_id' => ['required', 'integer', 'exists:groups,id'],
        ]);

        DB::transaction(function () {
            $studentData = [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'dni' => $this->dni,
                'birth_date' => $this->birth_date,
                'gender' => $this->gender,
                'is_active' => $this->is_active,
                'scholarship_percentage' => $this->scholarship_percentage,
            ];

            if ($this->editing) {
                $this->editing->update($studentData);
                $student = $this->editing;
            } else {
                $student = Student::create($studentData);
            }

            if (count($this->selected_tutor_ids) > 0) {
                $syncData = [];
                foreach (array_values($this->selected_tutor_ids) as $index => $id) {
                    $syncData[$id] = [
                        'relationship_type' => 'tutor_legal',
                        'is_primary' => $index === 0,
                    ];
                }
                $student->tutors()->sync($syncData);
            } else {
                $student->tutors()->detach();
            }

            $student->groups()->sync([
                $this->group_id => [
                    'from_date' => now()->toDateString(),
                    'to_date' => null,
                    'is_current' => true,
                ],
            ]);
        });

        $this->resetForm();
        $this->resetTutorSelection();
        $this->editing = null;
    }

    public function addNewTutor(): void
    {
        $this->validate([
            'new_tutor_first_name' => ['required', 'string', 'max:100'],
            'new_tutor_last_name' => ['required', 'string', 'max:100'],
            'new_tutor_email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'new_tutor_phone' => ['required', 'string', 'max:30'],
            'new_tutor_dni' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () {
            $user = User::create([
                'name' => trim($this->new_tutor_first_name . ' ' . $this->new_tutor_last_name),
                'email' => $this->new_tutor_email,
                'password' => Hash::make('juvefutbol'),
                'must_change_password' => true,
                'role' => 'tutor',
                'is_active' => true,
            ]);

            $tutor = Tutor::create([
                'user_id' => $user->id,
                'first_name' => $this->new_tutor_first_name,
                'last_name' => $this->new_tutor_last_name,
                'dni' => $this->new_tutor_dni !== '' ? $this->new_tutor_dni : null,
                'phone_main' => $this->new_tutor_phone,
            ]);

            $this->selected_tutor_ids[] = $tutor->id;
            $this->selected_tutor_ids = array_values(array_unique($this->selected_tutor_ids));
        });

        $this->resetNewTutor();
        session()->flash('tutor_message', 'Tutor creado y asignado al alumno en edición.');
    }

    public function delete(int $id)
    {
        Student::findOrFail($id)->delete();
        if ($this->editing && $this->editing->id === $id) {
            $this->resetForm();
            $this->editing = null;
        }
    }

    public function startEditingTutor(int $tutorId): void
    {
        $tutor = Tutor::with('user:id,email')->findOrFail($tutorId);

        $this->editingTutor = $tutor;
        $this->editing_tutor_first_name = (string) $tutor->first_name;
        $this->editing_tutor_last_name = (string) $tutor->last_name;
        $this->editing_tutor_dni = (string) ($tutor->dni ?? '');
        $this->editing_tutor_phone = (string) ($tutor->phone_main ?? '');
        $this->editing_tutor_email = (string) optional($tutor->user)->email;
    }

    public function saveEditingTutor(): void
    {
        if (! $this->editingTutor) {
            return;
        }

        $userId = $this->editingTutor->user_id;

        $this->validate([
            'editing_tutor_first_name' => ['required', 'string', 'max:100'],
            'editing_tutor_last_name' => ['required', 'string', 'max:100'],
            'editing_tutor_email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'editing_tutor_phone' => ['required', 'string', 'max:30'],
            'editing_tutor_dni' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($userId) {
            if ($userId) {
                $user = User::findOrFail($userId);
                $user->update([
                    'name' => trim($this->editing_tutor_first_name . ' ' . $this->editing_tutor_last_name),
                    'email' => $this->editing_tutor_email,
                ]);
            }

            $this->editingTutor->update([
                'first_name' => $this->editing_tutor_first_name,
                'last_name' => $this->editing_tutor_last_name,
                'dni' => $this->editing_tutor_dni !== '' ? $this->editing_tutor_dni : null,
                'phone_main' => $this->editing_tutor_phone,
            ]);
        });

        session()->flash('tutor_message', 'Tutor actualizado correctamente.');

        $this->cancelEditingTutor();
    }

    public function deleteTutor(int $tutorId): void
    {
        $tutor = Tutor::withCount('students')->findOrFail($tutorId);

        if ($tutor->students_count > 0) {
            session()->flash('tutor_error', 'No se puede eliminar un tutor que tiene alumnos asignados.');
            return;
        }

        DB::transaction(function () use ($tutor) {
            $userId = $tutor->user_id;

            $tutor->delete();

            if ($userId) {
                User::whereKey($userId)->delete();
            }
        });

        if ($this->selected_tutor_id === $tutorId) {
            $this->clearTutor();
        }

        if ($this->editingTutor && $this->editingTutor->id === $tutorId) {
            $this->cancelEditingTutor();
        }

        session()->flash('tutor_message', 'Tutor eliminado correctamente.');
    }

    public function cancelEditingTutor(): void
    {
        $this->editingTutor = null;
        $this->editing_tutor_first_name = '';
        $this->editing_tutor_last_name = '';
        $this->editing_tutor_email = '';
        $this->editing_tutor_phone = '';
        $this->editing_tutor_dni = '';
    }

    protected function resetForm(): void
    {
        $this->first_name = '';
        $this->last_name = '';
        $this->dni = '';
        $this->birth_date = '';
        $this->gender = null;
        $this->is_active = true;
        $this->scholarship_percentage = 0;
        $this->group_id = null;
        $this->tutor_search = '';
        $this->selected_tutor_ids = [];
        $this->show_new_tutor = false;
        $this->resetNewTutor();
    }

    protected function tutorResults()
    {
        $q = trim($this->tutor_search);
        if ($q === '') {
            return [];
        }

        return Tutor::query()
            ->with('user:id,email')
            ->withCount('students')
            ->where(function ($query) use ($q) {
                $query
                    ->where('dni', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$q}%"));
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(8)
            ->get();
    }

    public function selectTutor(int $tutorId): void
    {
        if (in_array($tutorId, $this->selected_tutor_ids, true)) {
            $this->selected_tutor_ids = array_values(array_filter(
                $this->selected_tutor_ids,
                fn ($id) => $id !== $tutorId
            ));
        } else {
            $this->selected_tutor_ids[] = $tutorId;
            $this->selected_tutor_ids = array_values(array_unique($this->selected_tutor_ids));
        }
        $this->show_new_tutor = false;
    }

    public function clearTutor(): void
    {
        $this->selected_tutor_ids = [];
    }

    public function toggleNewTutor(): void
    {
        $this->show_new_tutor = ! $this->show_new_tutor;
        if (! $this->show_new_tutor) {
            $this->resetNewTutor();
        }
    }

    protected function hasNewTutorData(): bool
    {
        return trim($this->new_tutor_first_name) !== ''
            || trim($this->new_tutor_last_name) !== ''
            || trim($this->new_tutor_email) !== ''
            || trim($this->new_tutor_phone) !== ''
            || trim($this->new_tutor_dni) !== '';
    }

    protected function resetNewTutor(): void
    {
        $this->new_tutor_first_name = '';
        $this->new_tutor_last_name = '';
        $this->new_tutor_email = '';
        $this->new_tutor_phone = '';
        $this->new_tutor_dni = '';
    }

    protected function resetTutorSelection(): void
    {
        $this->tutor_search = '';
        $this->selected_tutor_ids = [];
        $this->show_new_tutor = false;
        $this->resetNewTutor();
    }
}

