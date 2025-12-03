<?php

namespace Database\Seeders;

use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\User;
use Illuminate\Database\Seeder;

class CriterioSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'docente@demo.com')->first();

        if (!$user) {
            return;
        }

        $criteriosPorCampo = [
            'Lenguajes' => [
                ['nombre' => 'Comprensión lectora', 'porcentaje' => 25, 'descripcion' => 'Capacidad para entender y analizar textos escritos'],
                ['nombre' => 'Expresión escrita', 'porcentaje' => 25, 'descripcion' => 'Habilidad para comunicar ideas por escrito de forma clara y coherente'],
                ['nombre' => 'Expresión oral', 'porcentaje' => 25, 'descripcion' => 'Capacidad para comunicarse verbalmente con claridad'],
                ['nombre' => 'Expresión artística', 'porcentaje' => 25, 'descripcion' => 'Creatividad y expresión a través de las artes'],
            ],
            'Saberes y Pensamiento Científico' => [
                ['nombre' => 'Razonamiento matemático', 'porcentaje' => 30, 'descripcion' => 'Capacidad para resolver problemas matemáticos'],
                ['nombre' => 'Operaciones numéricas', 'porcentaje' => 25, 'descripcion' => 'Dominio de operaciones básicas y avanzadas'],
                ['nombre' => 'Pensamiento científico', 'porcentaje' => 25, 'descripcion' => 'Comprensión de fenómenos naturales y método científico'],
                ['nombre' => 'Experimentación', 'porcentaje' => 20, 'descripcion' => 'Habilidad para realizar y documentar experimentos'],
            ],
            'Ética, Naturaleza y Sociedades' => [
                ['nombre' => 'Conocimiento histórico', 'porcentaje' => 25, 'descripcion' => 'Comprensión de eventos y procesos históricos'],
                ['nombre' => 'Geografía y entorno', 'porcentaje' => 25, 'descripcion' => 'Conocimiento del espacio geográfico y medio ambiente'],
                ['nombre' => 'Formación cívica', 'porcentaje' => 25, 'descripcion' => 'Comprensión de derechos, deberes y valores ciudadanos'],
                ['nombre' => 'Participación comunitaria', 'porcentaje' => 25, 'descripcion' => 'Involucramiento en actividades de beneficio social'],
            ],
            'De lo Humano y lo Comunitario' => [
                ['nombre' => 'Desarrollo físico', 'porcentaje' => 30, 'descripcion' => 'Habilidades motrices y condición física'],
                ['nombre' => 'Vida saludable', 'porcentaje' => 25, 'descripcion' => 'Hábitos de alimentación, higiene y autocuidado'],
                ['nombre' => 'Desarrollo socioemocional', 'porcentaje' => 25, 'descripcion' => 'Manejo de emociones y relaciones interpersonales'],
                ['nombre' => 'Trabajo colaborativo', 'porcentaje' => 20, 'descripcion' => 'Capacidad para trabajar en equipo'],
            ],
        ];

        foreach ($criteriosPorCampo as $nombreCampo => $criterios) {
            $campo = CampoFormativo::withoutGlobalScopes()
                ->where('nombre', $nombreCampo)
                ->where('user_id', $user->id)
                ->first();

            if (!$campo) {
                continue;
            }

            $orden = 1;
            foreach ($criterios as $criterioData) {
                Criterio::firstOrCreate(
                    [
                        'nombre' => $criterioData['nombre'],
                        'campo_formativo_id' => $campo->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'porcentaje' => $criterioData['porcentaje'],
                        'descripcion' => $criterioData['descripcion'],
                        'orden' => $orden,
                    ]
                );
                $orden++;
            }
        }
    }
}
