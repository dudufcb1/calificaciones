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
        // Agregar la columna user_id a la tabla momentos si no existe
        if (!Schema::hasColumn('momentos', 'user_id')) {
            Schema::table('momentos', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            });
        }

        // Asigna los registros existentes al usuario autenticado o al primer administrador
        $adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $adminUser = \App\Models\User::first();
        }

        if ($adminUser) {
            // Actualizar los ciclos que no tengan un user_id asignado
            \App\Models\Ciclo::whereNull('user_id')->update(['user_id' => $adminUser->id]);

            // Asignar user_id a los momentos si la columna existe
            if (Schema::hasColumn('momentos', 'user_id')) {
                \App\Models\Momento::whereNull('user_id')->update(['user_id' => $adminUser->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo eliminar la columna si la añadimos en esta migración
        if (Schema::hasColumn('momentos', 'user_id')) {
            Schema::table('momentos', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
