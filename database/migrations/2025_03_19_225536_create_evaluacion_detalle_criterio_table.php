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
        Schema::create('evaluacion_detalle_criterio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_detalle_id')->constrained('evaluacion_detalles')->onDelete('cascade');
            $table->foreignId('criterio_id')->constrained('criterios')->onDelete('cascade');
            $table->decimal('calificacion', 5, 2);
            $table->decimal('calificacion_ponderada', 5, 2);
            $table->timestamps();

            // Índice único para evitar duplicados de criterios en el mismo detalle
            $table->unique(['evaluacion_detalle_id', 'criterio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_detalle_criterio');
    }
};
