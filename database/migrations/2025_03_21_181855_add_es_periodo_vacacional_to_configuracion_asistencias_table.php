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
        if (!Schema::hasColumn('configuracion_asistencias', 'es_periodo_vacacional')) {
            Schema::table('configuracion_asistencias', function (Blueprint $table) {
                $table->boolean('es_periodo_vacacional')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminamos el campo ya que es parte de la estructura principal
    }
};
