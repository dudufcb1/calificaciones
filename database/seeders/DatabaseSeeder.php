<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\Ciclo;
use App\Models\Momento;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario administrador si no existe
        $user = \App\Models\User::firstOrCreate(
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

        // Verificar si ya existe un usuario de prueba
        if (User::where('email', 'test@example.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Verificar si ya hay grupos creados
        if (Grupo::count() === 0) {
            // Crear grupos
            $grupo1 = Grupo::create([
                'nombre' => 'Grupo A',
                'descripcion' => 'Primer grado grupo A',
                'user_id' => $user->id
            ]);

            $grupo2 = Grupo::create([
                'nombre' => 'Grupo B',
                'descripcion' => 'Primer grado grupo B',
                'user_id' => $user->id
            ]);

            // Crear alumnos
            Alumno::create([
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'García',
                'grupo_id' => $grupo1->id,
                'estado' => 'activo',
                'user_id' => $user->id
            ]);

            Alumno::create([
                'nombre' => 'María',
                'apellido_paterno' => 'González',
                'apellido_materno' => 'López',
                'grupo_id' => $grupo1->id,
                'estado' => 'activo',
                'user_id' => $user->id
            ]);

            Alumno::create([
                'nombre' => 'Pedro',
                'apellido_paterno' => 'Ramírez',
                'apellido_materno' => 'Sánchez',
                'grupo_id' => $grupo2->id,
                'estado' => 'activo',
                'user_id' => $user->id
            ]);
        }

        // Verificar si ya hay campos formativos creados
        if (CampoFormativo::count() === 0) {
            $this->crearCamposFormativosConAsistencia($user);
        }

        // Crear ciclo escolar si no existe ninguno
        if (Ciclo::count() === 0) {
            $ciclo = Ciclo::create([
                'nombre' => 'Ciclo 2023-2024',
                'anio_inicio' => 2023,
                'anio_fin' => 2024,
                'activo' => true,
                'user_id' => $user->id
            ]);

            // Crear momentos de evaluación
            $momento1 = Momento::create([
                'nombre' => 'Primer Momento',
                'fecha' => '2023-10-01',
                'fecha_inicio' => '2023-08-01',
                'fecha_fin' => '2023-12-15',
                'ciclo_id' => $ciclo->id,
                'user_id' => $user->id
            ]);

            $momento2 = Momento::create([
                'nombre' => 'Segundo Momento',
                'fecha' => '2024-03-01',
                'fecha_inicio' => '2024-01-08',
                'fecha_fin' => '2024-06-30',
                'ciclo_id' => $ciclo->id,
                'user_id' => $user->id
            ]);

            // Asociar todos los campos formativos a ambos momentos
            $camposFormativos = CampoFormativo::all();
            $momento1->camposFormativos()->attach($camposFormativos->pluck('id'));
            $momento2->camposFormativos()->attach($camposFormativos->pluck('id'));
        }
    }

    /**
     * Crear campos formativos con criterio de asistencia por defecto
     */
    private function crearCamposFormativosConAsistencia($user)
    {
        $camposFormativos = [
            [
                'nombre' => 'Lenguaje y Comunicación',
                'descripcion' => 'Desarrollo de habilidades comunicativas y lingüísticas',
                'criterios' => [
                    ['nombre' => 'Expresión Oral', 'descripcion' => 'Capacidad para expresar ideas verbalmente', 'porcentaje' => 30],
                    ['nombre' => 'Comprensión Lectora', 'descripcion' => 'Capacidad para entender textos escritos', 'porcentaje' => 35],
                    ['nombre' => 'Producción Escrita', 'descripcion' => 'Capacidad para redactar textos', 'porcentaje' => 25],
                ]
            ],
            [
                'nombre' => 'Pensamiento Matemático',
                'descripcion' => 'Desarrollo del razonamiento matemático y numérico',
                'criterios' => [
                    ['nombre' => 'Resolución de Problemas', 'descripcion' => 'Capacidad para solucionar problemas matemáticos', 'porcentaje' => 40],
                    ['nombre' => 'Razonamiento Lógico', 'descripcion' => 'Capacidad de análisis y deducción', 'porcentaje' => 35],
                    ['nombre' => 'Cálculo Mental', 'descripcion' => 'Habilidad para realizar operaciones mentalmente', 'porcentaje' => 15],
                ]
            ],
            [
                'nombre' => 'Exploración y Comprensión del Mundo Natural y Social',
                'descripcion' => 'Conocimiento del entorno natural y social',
                'criterios' => [
                    ['nombre' => 'Observación Científica', 'descripcion' => 'Capacidad para observar y analizar fenómenos', 'porcentaje' => 30],
                    ['nombre' => 'Conocimiento Social', 'descripcion' => 'Comprensión del entorno social', 'porcentaje' => 35],
                    ['nombre' => 'Experimentación', 'descripcion' => 'Habilidad para realizar experimentos', 'porcentaje' => 25],
                ]
            ]
        ];

        foreach ($camposFormativos as $campoData) {
            // Crear el campo formativo
            $campo = CampoFormativo::create([
                'nombre' => $campoData['nombre'],
                'descripcion' => $campoData['descripcion'],
                'user_id' => $user->id
            ]);

            // Crear criterio de asistencia por defecto (sin porcentaje asignado)
            Criterio::create([
                'campo_formativo_id' => $campo->id,
                'nombre' => 'Asistencia',
                'descripcion' => 'Criterio de asistencia (programático)',
                'porcentaje' => 10, // 10% por defecto para asistencia
                'orden' => 0, // Primer orden para que aparezca primero
                'es_asistencia' => true,
                'user_id' => $user->id
            ]);

            // Crear los demás criterios
            foreach ($campoData['criterios'] as $index => $criterioData) {
                Criterio::create([
                    'campo_formativo_id' => $campo->id,
                    'nombre' => $criterioData['nombre'],
                    'descripcion' => $criterioData['descripcion'],
                    'porcentaje' => $criterioData['porcentaje'],
                    'orden' => $index + 1,
                    'es_asistencia' => false,
                    'user_id' => $user->id
                ]);
            }
        }
    }
}
