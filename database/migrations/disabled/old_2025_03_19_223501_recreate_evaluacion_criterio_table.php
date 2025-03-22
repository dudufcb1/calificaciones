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
        // Primero eliminamos la tabla pivot
        Schema::dropIfExists('evaluacion_criterio');

        // Luego la recreamos con las relaciones correctas
        Schema::create('evaluacion_criterio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluacion_id');
            $table->unsignedBigInteger('criterio_id');
            $table->decimal('calificacion', 5, 2);
            $table->decimal('calificacion_ponderada', 5, 2);
            $table->timestamps();

            // Establecer las relaciones correctas
            $table->foreign('evaluacion_id')
                  ->references('id')
                  ->on('evaluaciones')
                  ->onDelete('cascade');

            $table->foreign('criterio_id')
                  ->references('id')
                  ->on('criterios')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_criterio');

        // Recreamos la tabla con la configuración original
        Schema::create('evaluacion_criterio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained()->onDelete('cascade');
            $table->foreignId('criterio_id')->constrained()->onDelete('cascade');
            $table->decimal('calificacion', 5, 2);
            $table->decimal('calificacion_ponderada', 5, 2);
            $table->timestamps();
        });
    }
};
