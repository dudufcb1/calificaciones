<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\Evaluacion;
use Illuminate\Support\Facades\DB;

class AssignUserIdToExistingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si hay datos que necesitan actualización
        $alumnosSinUsuario = Alumno::whereNull('user_id')->count();
        $gruposSinUsuario = Grupo::whereNull('user_id')->count();
        $camposFormativosSinUsuario = CampoFormativo::whereNull('user_id')->count();
        $criteriosSinUsuario = Criterio::whereNull('user_id')->count();
        $evaluacionesSinUsuario = Evaluacion::whereNull('user_id')->count();

        $total = $alumnosSinUsuario + $gruposSinUsuario + $camposFormativosSinUsuario + $criteriosSinUsuario + $evaluacionesSinUsuario;

        if ($total === 0) {
            $this->command->info('No hay datos que requieran actualización de user_id');
            return;
        }

        // Obtener el primer usuario disponible o crear uno si no existe
        $user = User::first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Administrador',
                'email' => 'admin@example.com',
            ]);
            $this->command->info('Se ha creado un usuario administrador para asignar los datos existentes');
        }

        $userId = $user->id;

        // Actualizar todos los registros sin usuario asignado
        if ($alumnosSinUsuario > 0) {
            Alumno::whereNull('user_id')->update(['user_id' => $userId]);
            $this->command->info("Se actualizaron {$alumnosSinUsuario} alumnos");
        }

        if ($gruposSinUsuario > 0) {
            Grupo::whereNull('user_id')->update(['user_id' => $userId]);
            $this->command->info("Se actualizaron {$gruposSinUsuario} grupos");
        }

        if ($camposFormativosSinUsuario > 0) {
            CampoFormativo::whereNull('user_id')->update(['user_id' => $userId]);
            $this->command->info("Se actualizaron {$camposFormativosSinUsuario} campos formativos");
        }

        if ($criteriosSinUsuario > 0) {
            Criterio::whereNull('user_id')->update(['user_id' => $userId]);
            $this->command->info("Se actualizaron {$criteriosSinUsuario} criterios");
        }

        if ($evaluacionesSinUsuario > 0) {
            Evaluacion::whereNull('user_id')->update(['user_id' => $userId]);
            $this->command->info("Se actualizaron {$evaluacionesSinUsuario} evaluaciones");
        }

        $this->command->info('Se ha completado la asignación de user_id a todos los datos existentes');
    }
}
