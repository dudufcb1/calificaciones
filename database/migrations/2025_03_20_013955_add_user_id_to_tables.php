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
        // Añadir user_id a la tabla alumnos
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
        });

        // Añadir user_id a la tabla grupos
        Schema::table('grupos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
        });

        // Añadir user_id a la tabla campo_formativos
        Schema::table('campo_formativos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
        });

        // Añadir user_id a la tabla evaluaciones
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
        });

        // Añadir user_id a la tabla criterios
        Schema::table('criterios', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->after('id')->onDelete('cascade');
        });

        // Actualizar registros existentes con el primer usuario disponible (si existe)
        if (Schema::hasTable('users') && \DB::table('users')->count() > 0) {
            $userId = \DB::table('users')->first()->id;

            \DB::table('alumnos')->update(['user_id' => $userId]);
            \DB::table('grupos')->update(['user_id' => $userId]);
            \DB::table('campo_formativos')->update(['user_id' => $userId]);
            \DB::table('evaluaciones')->update(['user_id' => $userId]);
            \DB::table('criterios')->update(['user_id' => $userId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar user_id de la tabla alumnos
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Eliminar user_id de la tabla grupos
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Eliminar user_id de la tabla campo_formativos
        Schema::table('campo_formativos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Eliminar user_id de la tabla evaluaciones
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Eliminar user_id de la tabla criterios
        Schema::table('criterios', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
