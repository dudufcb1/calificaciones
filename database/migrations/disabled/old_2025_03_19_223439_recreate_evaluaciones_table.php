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
        // Primero eliminamos las tablas que tienen claves foráneas a evaluaciones
        Schema::table('evaluacion_criterio', function (Blueprint $table) {
            $table->dropForeign(['evaluacion_id']);
        });

        // Luego eliminamos la tabla evaluaciones
        Schema::dropIfExists('evaluaciones');

        // Recreamos la tabla con las relaciones correctas
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campo_formativo_id');
            $table->unsignedBigInteger('alumno_id');
            $table->decimal('promedio_final', 5, 2)->default(0);
            $table->boolean('is_draft')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Establecer las relaciones correctas de manera explícita
            $table->foreign('campo_formativo_id')
                  ->references('id')
                  ->on('campo_formativos')
                  ->onDelete('cascade');

            $table->foreign('alumno_id')
                  ->references('id')
                  ->on('alumnos')
                  ->onDelete('cascade');
        });

        // Volvemos a crear las claves foráneas en evaluacion_criterio
        Schema::table('evaluacion_criterio', function (Blueprint $table) {
            $table->foreign('evaluacion_id')
                  ->references('id')
                  ->on('evaluaciones')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluaciones');

        // Recreamos la tabla con la configuración original
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_formativo_id')->constrained('campo_formativos')->onDelete('cascade');
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->decimal('promedio_final', 5, 2)->default(0);
            $table->boolean('is_draft')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
