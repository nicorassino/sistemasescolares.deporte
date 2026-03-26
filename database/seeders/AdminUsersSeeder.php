<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            ['email' => 'admin', 'name' => 'admin', 'password' => 'teto3'],
            ['email' => 'adminjuve', 'name' => 'adminjuve', 'password' => '3160'],
        ];

        foreach ($admins as $admin) {
            /** @var User|null $user */
            $user = User::where('email', $admin['email'])->first();

            if (! $user) {
                User::create([
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make($admin['password']),
                    'role' => 'admin',
                    'is_active' => true,
                    'last_login_at' => null,
                ]);
                continue;
            }

            $user->update([
                'name' => $admin['name'],
                'password' => Hash::make($admin['password']),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        }
    }
}

