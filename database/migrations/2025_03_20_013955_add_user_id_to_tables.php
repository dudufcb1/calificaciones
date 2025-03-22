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
        // Añadir user_id a la tabla grupos si no existe
        if (!Schema::hasColumn('grupos', 'user_id')) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
            });
        }

        // Añadir user_id a la tabla campo_formativos si no existe
        if (!Schema::hasColumn('campo_formativos', 'user_id')) {
            Schema::table('campo_formativos', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
            });
        }

        // Añadir user_id a la tabla criterios si no existe
        if (!Schema::hasColumn('criterios', 'user_id')) {
            Schema::table('criterios', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
            });
        }

        // Actualizar registros existentes con el primer usuario disponible (si existe)
        if (Schema::hasTable('users') && \DB::table('users')->count() > 0) {
            $userId = \DB::table('users')->first()->id;

            \DB::table('grupos')->whereNull('user_id')->update(['user_id' => $userId]);
            \DB::table('campo_formativos')->whereNull('user_id')->update(['user_id' => $userId]);
            \DB::table('criterios')->whereNull('user_id')->update(['user_id' => $userId]);
            \DB::table('alumnos')->whereNull('user_id')->update(['user_id' => $userId]);
            \DB::table('evaluaciones')->whereNull('user_id')->update(['user_id' => $userId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nada que revertir para alumnos y evaluaciones ya que tienen user_id en sus migraciones principales

        // Eliminar user_id de la tabla grupos si existe
        if (Schema::hasColumn('grupos', 'user_id')) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Eliminar user_id de la tabla campo_formativos si existe
        if (Schema::hasColumn('campo_formativos', 'user_id')) {
            Schema::table('campo_formativos', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        // Eliminar user_id de la tabla criterios si existe
        if (Schema::hasColumn('criterios', 'user_id')) {
            Schema::table('criterios', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
