<?php

namespace Database\Seeders;

use App\Models\Ciclo;
use App\Models\User;
use Illuminate\Database\Seeder;

class CicloEscolarSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'docente@demo.com')->first();

        if (!$user) {
            return;
        }

        Ciclo::firstOrCreate(
            [
                'anio_inicio' => 2024,
                'anio_fin' => 2025,
                'user_id' => $user->id,
            ],
            [
                'nombre' => 'Ciclo Escolar',
                'activo' => true,
            ]
        );
    }
}
