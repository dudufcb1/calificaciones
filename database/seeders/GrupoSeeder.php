<?php

namespace Database\Seeders;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Seeder;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'docente@demo.com')->first();

        if (!$user) {
            return;
        }

        $grupos = [
            ['nombre' => '1-A', 'descripcion' => 'Primer grado grupo A - Fase 3'],
            ['nombre' => '1-B', 'descripcion' => 'Primer grado grupo B - Fase 3'],
            ['nombre' => '2-A', 'descripcion' => 'Segundo grado grupo A - Fase 3'],
            ['nombre' => '2-B', 'descripcion' => 'Segundo grado grupo B - Fase 3'],
            ['nombre' => '3-A', 'descripcion' => 'Tercer grado grupo A - Fase 4'],
            ['nombre' => '3-B', 'descripcion' => 'Tercer grado grupo B - Fase 4'],
            ['nombre' => '4-A', 'descripcion' => 'Cuarto grado grupo A - Fase 4'],
            ['nombre' => '4-B', 'descripcion' => 'Cuarto grado grupo B - Fase 4'],
            ['nombre' => '5-A', 'descripcion' => 'Quinto grado grupo A - Fase 5'],
            ['nombre' => '5-B', 'descripcion' => 'Quinto grado grupo B - Fase 5'],
            ['nombre' => '6-A', 'descripcion' => 'Sexto grado grupo A - Fase 5'],
            ['nombre' => '6-B', 'descripcion' => 'Sexto grado grupo B - Fase 5'],
        ];

        foreach ($grupos as $grupo) {
            Grupo::firstOrCreate(
                [
                    'nombre' => $grupo['nombre'],
                    'user_id' => $user->id,
                ],
                [
                    'descripcion' => $grupo['descripcion'],
                ]
            );
        }
    }
}
