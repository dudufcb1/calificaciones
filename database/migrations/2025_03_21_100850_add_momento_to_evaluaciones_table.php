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
        if (!Schema::hasColumn('evaluaciones', 'momento')) {
            Schema::table('evaluaciones', function (Blueprint $table) {
                $table->string('momento')->nullable()->after('fecha_evaluacion');
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
