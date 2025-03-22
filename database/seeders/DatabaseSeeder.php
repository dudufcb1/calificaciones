<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\Ciclo;
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

        // Ejecutar los seeders con el usuario administrador como contexto
        $this->call([
            AssignUserIdToExistingDataSeeder::class,
        ]);

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
            // Crear campos formativos
            $campo1 = CampoFormativo::create([
                'nombre' => 'Lenguaje y Comunicación',
                'descripcion' => 'Desarrollo de habilidades comunicativas',
                'user_id' => $user->id
            ]);

            $campo2 = CampoFormativo::create([
                'nombre' => 'Pensamiento Matemático',
                'descripcion' => 'Desarrollo de habilidades lógico-matemáticas',
                'user_id' => $user->id
            ]);

            // Crear criterios de evaluación
            Criterio::create([
                'campo_formativo_id' => $campo1->id,
                'nombre' => 'Expresión oral',
                'descripcion' => 'Capacidad para expresar ideas verbalmente',
                'porcentaje' => 30,
                'orden' => 1,
                'user_id' => $user->id
            ]);

            Criterio::create([
                'campo_formativo_id' => $campo1->id,
                'nombre' => 'Comprensión lectora',
                'descripcion' => 'Capacidad para entender textos escritos',
                'porcentaje' => 40,
                'orden' => 2,
                'user_id' => $user->id
            ]);

            Criterio::create([
                'campo_formativo_id' => $campo1->id,
                'nombre' => 'Producción escrita',
                'descripcion' => 'Capacidad para redactar textos',
                'porcentaje' => 30,
                'orden' => 3,
                'user_id' => $user->id
            ]);

            Criterio::create([
                'campo_formativo_id' => $campo2->id,
                'nombre' => 'Resolución de problemas',
                'descripcion' => 'Capacidad para solucionar problemas matemáticos',
                'porcentaje' => 50,
                'orden' => 1,
                'user_id' => $user->id
            ]);

            Criterio::create([
                'campo_formativo_id' => $campo2->id,
                'nombre' => 'Razonamiento lógico',
                'descripcion' => 'Capacidad de análisis y deducción',
                'porcentaje' => 50,
                'orden' => 2,
                'user_id' => $user->id
            ]);
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
        }
    }
}
