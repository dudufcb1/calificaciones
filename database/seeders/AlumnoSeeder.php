<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AlumnoSeeder extends Seeder
{
    private array $nombres = [
        'masculino' => [
            'Santiago', 'Mateo', 'Sebastián', 'Leonardo', 'Emiliano', 'Diego', 'Miguel Ángel',
            'Daniel', 'Pablo', 'Alejandro', 'José Luis', 'Carlos', 'Luis', 'Jorge', 'Ángel',
            'Fernando', 'Ricardo', 'Eduardo', 'Adrián', 'Juan Pablo', 'Andrés', 'Rodrigo',
            'Héctor', 'Óscar', 'Bruno', 'Gael', 'Ian', 'Iker', 'Liam', 'Noah',
        ],
        'femenino' => [
            'Sofía', 'Valentina', 'Regina', 'Camila', 'María José', 'Ximena', 'Mariana',
            'Fernanda', 'Daniela', 'Luciana', 'Victoria', 'Renata', 'Natalia', 'Isabella',
            'Andrea', 'Paula', 'Valeria', 'Ana Sofía', 'María Fernanda', 'Alejandra',
            'Gabriela', 'Carolina', 'Elena', 'Jimena', 'Montserrat', 'Alondra', 'Abril',
            'Emma', 'Mía', 'Luna',
        ],
    ];

    private array $apellidos = [
        'García', 'Hernández', 'López', 'Martínez', 'González', 'Rodríguez', 'Pérez',
        'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Cruz',
        'Morales', 'Reyes', 'Ortiz', 'Gutiérrez', 'Chávez', 'Ramos', 'Vargas', 'Castillo',
        'Jiménez', 'Moreno', 'Romero', 'Herrera', 'Medina', 'Aguilar', 'Vázquez',
        'Mendoza', 'Ruiz', 'Castro', 'Núñez', 'Álvarez', 'Contreras', 'Guerrero',
        'Rojas', 'Salazar', 'Luna', 'Delgado', 'Sandoval', 'Cervantes', 'Domínguez',
        'Suárez', 'Espinoza', 'Ríos', 'Cabrera', 'Campos', 'Navarro',
    ];

    private array $tutores = [
        'masculino' => ['Roberto', 'Francisco', 'Manuel', 'Arturo', 'Enrique', 'Raúl', 'Gerardo'],
        'femenino' => ['María', 'Rosa', 'Patricia', 'Claudia', 'Silvia', 'Leticia', 'Gloria'],
    ];

    public function run(): void
    {
        $user = User::where('email', 'docente@demo.com')->first();

        if (!$user) {
            return;
        }

        $grupos = Grupo::withoutGlobalScopes()->where('user_id', $user->id)->get();

        foreach ($grupos as $grupo) {
            $alumnosPorGrupo = rand(20, 30);
            $grado = (int) substr($grupo->nombre, 0, 1);

            for ($i = 0; $i < $alumnosPorGrupo; $i++) {
                $genero = rand(0, 1) ? 'masculino' : 'femenino';
                $nombre = $this->nombres[$genero][array_rand($this->nombres[$genero])];
                $apellidoPaterno = $this->apellidos[array_rand($this->apellidos)];
                $apellidoMaterno = $this->apellidos[array_rand($this->apellidos)];

                $fechaNacimiento = $this->generarFechaNacimiento($grado);
                $curp = $this->generarCURP($nombre, $apellidoPaterno, $apellidoMaterno, $fechaNacimiento, $genero);

                $generoTutor = rand(0, 1) ? 'masculino' : 'femenino';
                $tutorNombre = $this->tutores[$generoTutor][array_rand($this->tutores[$generoTutor])] . ' ' . $apellidoPaterno;

                Alumno::create([
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                    'grupo_id' => $grupo->id,
                    'estado' => 'activo',
                    'user_id' => $user->id,
                    'curp' => $curp,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'genero' => $genero,
                    'tutor_nombre' => $tutorNombre,
                    'tutor_email' => strtolower(str_replace(' ', '.', $tutorNombre)) . '@ejemplo.com',
                    'direccion' => $this->generarDireccion(),
                    'telefono_emergencia' => $this->generarTelefono(),
                    'telefono_tutor' => $this->generarTelefono(),
                    'alergias' => rand(0, 10) < 2 ? $this->generarAlergia() : null,
                    'observaciones' => rand(0, 10) < 1 ? 'Requiere atención especial' : null,
                ]);
            }
        }
    }

    private function generarFechaNacimiento(int $grado): Carbon
    {
        $anioActual = 2025;
        $edadBase = 6 + ($grado - 1);
        $anioNacimiento = $anioActual - $edadBase - 1;

        return Carbon::create($anioNacimiento, rand(1, 12), rand(1, 28));
    }

    private function generarCURP(string $nombre, string $paterno, string $materno, Carbon $fecha, string $genero): string
    {
        $paterno = $this->normalizarTexto($paterno);
        $materno = $this->normalizarTexto($materno);
        $nombre = $this->normalizarTexto($nombre);

        $curp = strtoupper(substr($paterno, 0, 2));
        $curp .= strtoupper($this->primeraVocal(substr($paterno, 1)));
        $curp .= strtoupper(substr($materno, 0, 1));
        $curp .= $fecha->format('ymd');
        $curp .= $genero === 'masculino' ? 'H' : 'M';
        $estados = ['AS', 'BC', 'BS', 'CC', 'CS', 'CH', 'CL', 'CM', 'DF', 'DG', 'GT', 'GR', 'HG', 'JC', 'MC', 'MN', 'MS', 'NT', 'NL', 'OC', 'PL', 'QT', 'QR', 'SP', 'SL', 'SR', 'TC', 'TS', 'TL', 'VZ', 'YN', 'ZS'];
        $curp .= $estados[array_rand($estados)];
        $curp .= strtoupper($this->primeraConsonante(substr($paterno, 1)));
        $curp .= strtoupper($this->primeraConsonante(substr($materno, 1)));
        $curp .= strtoupper($this->primeraConsonante(substr($nombre, 1)));
        $curp .= chr(rand(65, 90));
        $curp .= rand(0, 9);

        return $curp;
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['A', 'E', 'I', 'O', 'U', 'X', 'a', 'e', 'i', 'o', 'u', 'x'],
            $texto
        );
        return preg_replace('/[^A-Za-z]/', '', $texto);
    }

    private function primeraVocal(string $texto): string
    {
        preg_match('/[aeiouAEIOU]/', $texto, $matches);
        return $matches[0] ?? 'X';
    }

    private function primeraConsonante(string $texto): string
    {
        preg_match('/[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]/', $texto, $matches);
        return $matches[0] ?? 'X';
    }

    private function generarDireccion(): string
    {
        $calles = ['Av. Insurgentes', 'Calle Hidalgo', 'Calle Juárez', 'Av. Reforma', 'Calle Morelos', 'Av. Revolución', 'Calle 5 de Mayo', 'Calle Independencia', 'Av. Universidad', 'Calle Benito Juárez'];
        $colonias = ['Centro', 'Del Valle', 'Roma Norte', 'Condesa', 'Polanco', 'Coyoacán', 'San Ángel', 'Narvarte', 'Santa Fe', 'Tlalpan'];

        return $calles[array_rand($calles)] . ' #' . rand(1, 500) . ', Col. ' . $colonias[array_rand($colonias)];
    }

    private function generarTelefono(): string
    {
        $ladas = ['55', '33', '81', '442', '222', '664', '656', '614', '999', '229'];
        return $ladas[array_rand($ladas)] . rand(1000, 9999) . rand(1000, 9999);
    }

    private function generarAlergia(): string
    {
        $alergias = ['Polen', 'Polvo', 'Nueces', 'Mariscos', 'Lácteos', 'Gluten', 'Picadura de abeja', 'Penicilina'];
        return $alergias[array_rand($alergias)];
    }
}
