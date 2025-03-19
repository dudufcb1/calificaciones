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
            $table->string('curp', 18)->nullable()->change();
            $table->date('fecha_nacimiento')->nullable()->change();
            $table->string('genero')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('curp', 18)->nullable(false)->change();
            $table->date('fecha_nacimiento')->nullable(false)->change();
            $table->string('genero')->nullable(false)->change();
        });
    }
};
