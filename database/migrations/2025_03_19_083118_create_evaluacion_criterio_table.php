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
        Schema::create('evaluacion_criterio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained()->onDelete('cascade');
            $table->foreignId('criterio_id')->constrained('criterios')->onDelete('cascade');
            $table->decimal('calificacion', 5, 2)->default(0);
            $table->decimal('calificacion_ponderada', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_criterio');
    }
};
