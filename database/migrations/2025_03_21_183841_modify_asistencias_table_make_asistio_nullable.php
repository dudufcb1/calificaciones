<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para SQLite, el enfoque más seguro es actualizar la tabla directamente
        // En lugar de recrearla, vamos a añadir un valor por defecto a asistio basado en estado

        // Actualizar los valores de asistio según el estado
        DB::statement("UPDATE asistencias SET asistio =
            CASE
                WHEN estado = 'asistio' THEN 1
                WHEN estado = 'falta' THEN 0
                WHEN estado = 'justificada' THEN 0
                ELSE 1
            END
            WHERE asistio IS NULL");

        // En SQLite no podemos cambiar una columna a nullable directamente,
        // pero podemos trabajar con valores predeterminados
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay operación para deshacer
    }
};
