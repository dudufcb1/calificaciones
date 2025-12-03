<?php

namespace Database\Seeders;

use App\Models\CampoFormativo;
use App\Models\Ciclo;
use App\Models\Momento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MomentoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'docente@demo.com')->first();

        if (!$user) {
            return;
        }

        $ciclo = Ciclo::where('user_id', $user->id)
            ->where('anio_inicio', 2024)
            ->where('anio_fin', 2025)
            ->first();

        if (!$ciclo) {
            return;
        }

        $momentos = [
            [
                'nombre' => 'Primer Trimestre',
                'fecha' => Carbon::create(2024, 11, 22),
                'fecha_inicio' => Carbon::create(2024, 8, 26),
                'fecha_fin' => Carbon::create(2024, 11, 22),
            ],
            [
                'nombre' => 'Segundo Trimestre',
                'fecha' => Carbon::create(2025, 3, 7),
                'fecha_inicio' => Carbon::create(2024, 11, 25),
                'fecha_fin' => Carbon::create(2025, 3, 7),
            ],
            [
                'nombre' => 'Tercer Trimestre',
                'fecha' => Carbon::create(2025, 7, 16),
                'fecha_inicio' => Carbon::create(2025, 3, 10),
                'fecha_fin' => Carbon::create(2025, 7, 16),
            ],
        ];

        $camposFormativos = CampoFormativo::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->get();

        foreach ($momentos as $momentoData) {
            $momento = Momento::firstOrCreate(
                [
                    'nombre' => $momentoData['nombre'],
                    'ciclo_id' => $ciclo->id,
                    'user_id' => $user->id,
                ],
                [
                    'fecha' => $momentoData['fecha'],
                    'fecha_inicio' => $momentoData['fecha_inicio'],
                    'fecha_fin' => $momentoData['fecha_fin'],
                ]
            );

            $momento->camposFormativos()->syncWithoutDetaching($camposFormativos->pluck('id')->toArray());
        }
    }
}
