<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Los campos ya existen en la tabla, por lo que no necesitamos hacer nada
        // tutor_nombre, tutor_telefono, tutor_email, direccion, telefono_emergencia, alergias, observaciones
        // Esta migración se deja vacía para mantener consistencia en el historial de migraciones
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay nada que deshacer, los campos ya existían antes de esta migración
    }
};
