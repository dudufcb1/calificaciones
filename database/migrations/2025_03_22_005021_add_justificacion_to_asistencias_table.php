<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            // Agregar la columna justificacion si no existe
            if (!Schema::hasColumn('asistencias', 'justificacion')) {
                $table->text('justificacion')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            // Eliminar la columna justificacion si existe
            if (Schema::hasColumn('asistencias', 'justificacion')) {
                $table->dropColumn('justificacion');
            }
        });
    }
};
