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
    public ?int $selected_tutor_id = null;

    public bool $show_new_tutor = false;
    public string $new_tutor_first_name = '';
    public string $new_tutor_last_name = '';
    public string $new_tutor_email = '';
    public string $new_tutor_phone = '';
    public string $new_tutor_dni = '';

    public ?Student $editing = null;

    #[Layout('layouts.app')]
    public function render()
    {
        $studentsQuery = Student::with(['tutors', 'groups'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        if (trim($this->search) !== '') {
            $q = trim($this->search);
            $studentsQuery->where(function ($query) use ($q) {
                $query->where('last_name', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%");
            });
        }

        return view('livewire.admin.students-page', [
            'students' => $studentsQuery->get(),
            'groups' => Group::orderBy('name')->get(),
            'tutorResults' => $this->tutorResults(),
            'selectedTutor' => $this->selected_tutor_id ? Tutor::with('user:id,email')->find($this->selected_tutor_id) : null,
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
        $this->selected_tutor_id = $student->tutors->first()->id ?? null;
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

        $useNewTutor = $this->hasNewTutorData();

        if (! $useNewTutor) {
            $this->validate([
                'selected_tutor_id' => ['required', 'integer', 'exists:tutors,id'],
            ], [
                'selected_tutor_id.required' => 'Seleccioná un tutor existente o creá uno nuevo.',
            ]);
        } else {
            $this->validate([
                'new_tutor_first_name' => ['required', 'string', 'max:100'],
                'new_tutor_last_name' => ['required', 'string', 'max:100'],
                'new_tutor_email' => ['required', 'email', 'max:190', 'unique:users,email'],
                'new_tutor_phone' => ['required', 'string', 'max:30'],
                'new_tutor_dni' => ['nullable', 'string', 'max:20'],
            ]);
        }

        DB::transaction(function () use ($useNewTutor) {
            $tutorId = $this->selected_tutor_id;

            if ($useNewTutor) {
                $user = User::create([
                    'name' => trim($this->new_tutor_first_name . ' ' . $this->new_tutor_last_name),
                    'email' => $this->new_tutor_email,
                    'password' => Hash::make('pasepase'),
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

                $tutorId = $tutor->id;
            }

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

            $student->tutors()->sync([
                $tutorId => ['relationship_type' => 'tutor_legal', 'is_primary' => true],
            ]);

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

    public function delete(int $id)
    {
        Student::findOrFail($id)->delete();
        if ($this->editing && $this->editing->id === $id) {
            $this->resetForm();
            $this->editing = null;
        }
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
        $this->selected_tutor_id = null;
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
        $this->selected_tutor_id = $tutorId;
        $this->show_new_tutor = false;
    }

    public function clearTutor(): void
    {
        $this->selected_tutor_id = null;
    }

    public function toggleNewTutor(): void
    {
        $this->show_new_tutor = ! $this->show_new_tutor;
        if ($this->show_new_tutor) {
            $this->selected_tutor_id = null;
        } else {
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
        $this->selected_tutor_id = null;
        $this->show_new_tutor = false;
        $this->resetNewTutor();
    }
}

