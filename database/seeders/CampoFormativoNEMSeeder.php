<?php

namespace Database\Seeders;

use App\Models\CampoFormativo;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampoFormativoNEMSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'docente@demo.com')->first();

        if (!$user) {
            return;
        }

        $camposFormativos = [
            [
                'nombre' => 'Lenguajes',
                'descripcion' => 'Integra español, lenguas indígenas (si aplica), inglés y artes. Desarrollo de habilidades comunicativas en múltiples formas de expresión.',
            ],
            [
                'nombre' => 'Saberes y Pensamiento Científico',
                'descripcion' => 'Combina matemáticas y ciencias naturales. Desarrollo del razonamiento lógico-matemático y comprensión del mundo natural.',
            ],
            [
                'nombre' => 'Ética, Naturaleza y Sociedades',
                'descripcion' => 'Abarca geografía, historia y formación cívica. Comprensión del entorno social, histórico y desarrollo de valores ciudadanos.',
            ],
            [
                'nombre' => 'De lo Humano y lo Comunitario',
                'descripcion' => 'Incluye educación física, vida saludable y educación socioemocional. Desarrollo integral del bienestar físico, mental y social.',
            ],
        ];

        foreach ($camposFormativos as $campo) {
            CampoFormativo::firstOrCreate(
                [
                    'nombre' => $campo['nombre'],
                    'user_id' => $user->id,
                ],
                [
                    'descripcion' => $campo['descripcion'],
                ]
            );
        }
    }
}
