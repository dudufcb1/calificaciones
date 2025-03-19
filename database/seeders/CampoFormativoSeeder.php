<?php

namespace Database\Seeders;

use App\Models\CampoFormativo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampoFormativoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $camposFormativos = [
            [
                'nombre' => 'Lenguaje y Comunicación',
                'descripcion' => 'Desarrollo de habilidades comunicativas y lingüísticas'
            ],
            [
                'nombre' => 'Pensamiento Matemático',
                'descripcion' => 'Desarrollo del razonamiento matemático y numérico'
            ],
            [
                'nombre' => 'Exploración y Comprensión del Mundo Natural y Social',
                'descripcion' => 'Conocimiento del entorno natural y social'
            ],
            [
                'nombre' => 'Desarrollo Personal y Social',
                'descripcion' => 'Desarrollo de habilidades sociales y emocionales'
            ],
            [
                'nombre' => 'Artes',
                'descripcion' => 'Expresión artística y creativa'
            ],
            [
                'nombre' => 'Educación Física',
                'descripcion' => 'Desarrollo físico y motriz'
            ]
        ];

        foreach ($camposFormativos as $campo) {
            CampoFormativo::create($campo);
        }
    }
}
