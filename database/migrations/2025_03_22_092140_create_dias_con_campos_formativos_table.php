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
        Schema::create('dias_con_campos_formativos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->foreignId('campo_formativo_id')->constrained('campo_formativos')->onDelete('cascade');
            $table->index(['fecha', 'grupo_id', 'campo_formativo_id'], 'idx_dia_grupo_campo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_con_campos_formativos');
    }
};
