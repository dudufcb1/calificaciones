<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'coringasmx@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('M3lomelo!'),
                'email_verified_at' => now(),
                'status' => 'active',
                'role' => 'admin',
                'is_confirmed' => true,
            ]
        );

        $this->call([
            AssignUserIdToExistingDataSeeder::class,
        ]);

        $this->call([
            DemoUserSeeder::class,
            CicloEscolarSeeder::class,
            CampoFormativoNEMSeeder::class,
            CriterioSeeder::class,
            GrupoSeeder::class,
            AlumnoSeeder::class,
            MomentoSeeder::class,
        ]);
    }
}
