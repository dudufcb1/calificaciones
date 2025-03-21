<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Importar la clase DB

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->enum('estado', ['asistio', 'falta', 'justificada'])->default('asistio');
            $table->boolean('asistio')->nullable(); // Agregar la columna asistio
        });

        // Actualizar los valores de asistio según el estado
        DB::statement("UPDATE asistencias SET asistio =
            CASE
                WHEN estado = 'asistio' THEN 1
                WHEN estado = 'falta' THEN 0
                WHEN estado = 'justificada' THEN 0
                ELSE 1
            END
            WHERE asistio IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn('estado');
            $table->dropColumn('asistio'); // Asegurarse de eliminar la columna en el rollback
        });
    }
};