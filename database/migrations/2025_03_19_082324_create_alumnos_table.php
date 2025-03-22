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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->string('curp', 18)->nullable()->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            // Campos de información de tutor
            $table->string('tutor_nombre')->nullable();
            $table->string('tutor_telefono')->nullable();
            $table->string('tutor_email')->nullable();

            // Campos de información personal
            $table->text('direccion')->nullable();
            $table->string('telefono_emergencia')->nullable();
            $table->text('alergias')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
