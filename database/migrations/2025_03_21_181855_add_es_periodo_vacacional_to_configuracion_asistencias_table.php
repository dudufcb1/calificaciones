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
        Schema::table('configuracion_asistencias', function (Blueprint $table) {
            $table->boolean('es_periodo_vacacional')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_asistencias', function (Blueprint $table) {
            $table->dropColumn('es_periodo_vacacional');
        });
    }
};
