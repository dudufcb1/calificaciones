<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Administrador Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'status' => 'active',
                'is_confirmed' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'docente@demo.com'],
            [
                'name' => 'María Guadalupe Hernández López',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'user',
                'status' => 'active',
                'is_confirmed' => true,
            ]
        );
    }
}
