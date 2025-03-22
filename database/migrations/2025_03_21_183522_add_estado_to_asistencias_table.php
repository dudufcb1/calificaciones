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
        if (!Schema::hasColumn('asistencias', 'estado')) {
            Schema::table('asistencias', function (Blueprint $table) {
                $table->enum('estado', ['asistio', 'falta', 'justificada'])->default('asistio');
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
