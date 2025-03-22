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
        Schema::table('alumnos', function (Blueprint $table) {
            // Agregar el campo teléfono del tutor
            if (!Schema::hasColumn('alumnos', 'telefono_tutor')) {
                $table->string('telefono_tutor')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            if (Schema::hasColumn('alumnos', 'telefono_tutor')) {
                $table->dropColumn('telefono_tutor');
            }
        });
    }
};
