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
        Schema::table('evaluaciones', function (Blueprint $table) {
            // Primero eliminar la restricción de clave foránea existente
            $table->dropForeign(['campo_formativo_id']);

            // Luego añadir la nueva restricción con la tabla correcta
            $table->foreign('campo_formativo_id')
                  ->references('id')
                  ->on('campo_formativos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            // Eliminar la nueva restricción
            $table->dropForeign(['campo_formativo_id']);

            // Restaurar la restricción original (aunque apunte a una tabla que ya no existe)
            $table->foreign('campo_formativo_id')
                  ->references('id')
                  ->on('campos_formativos')
                  ->onDelete('cascade');
        });
    }
};
