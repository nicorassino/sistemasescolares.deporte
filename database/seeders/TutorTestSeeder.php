<?php

namespace Database\Seeders;

use App\Models\Fee;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TutorTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'padre@test.com'],
            [
                'name' => 'Padre Test',
                'password' => Hash::make('88668866'),
                'role' => 'tutor',
                'is_active' => true,
            ]
        );

        if (! $user->tutor) {
            $tutor = Tutor::create([
                'user_id' => $user->id,
                'first_name' => 'Padre',
                'last_name' => 'Test',
                'phone_main' => '1112345678',
            ]);
        } else {
            $tutor = $user->tutor;
        }

        $group = Group::orderBy('id')->first();
        if (! $group) {
            $group = Group::create([
                'name' => 'Grupo Test',
                'is_active' => true,
            ]);
        }

        $student = Student::firstOrCreate(
            ['dni' => '99999999'],
            [
                'first_name' => 'Alumno',
                'last_name' => 'Test Tutor',
                'birth_date' => '2015-01-01',
                'is_active' => true,
            ]
        );

        $tutor->students()->syncWithoutDetaching([
            $student->id => ['relationship_type' => 'padre', 'is_primary' => true],
        ]);

        $student->groups()->syncWithoutDetaching([
            $group->id => [
                'from_date' => now()->toDateString(),
                'to_date' => null,
                'is_current' => true,
            ],
        ]);

        Fee::firstOrCreate(
            [
                'student_id' => $student->id,
                'period' => now()->format('Y-m'),
            ],
            [
                'group_id' => $group->id,
                'type' => 'tuition',
                'amount' => 15000,
                'due_date' => now()->addDays(10),
                'status' => 'pending',
                'issued_at' => now(),
            ]
        );
    }
}
