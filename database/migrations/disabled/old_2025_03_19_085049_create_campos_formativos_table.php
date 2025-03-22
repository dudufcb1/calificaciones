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
        // Ya existe la tabla campo_formativos, no creamos una duplicada
        // Schema::create('campos_formativos', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('nombre');
        //     $table->text('descripcion')->nullable();
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('campos_formativos');
    }
};
