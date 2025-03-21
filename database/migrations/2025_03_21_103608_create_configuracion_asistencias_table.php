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
        Schema::create('configuracion_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('mes');
            $table->integer('anio');
            $table->integer('dias_habiles');
            $table->boolean('es_periodo_vacacional')->default(false);
            $table->timestamps();

            // Asegurar que no haya duplicados para el mismo mes y año por usuario
            $table->unique(['user_id', 'mes', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_asistencias');
    }
};
