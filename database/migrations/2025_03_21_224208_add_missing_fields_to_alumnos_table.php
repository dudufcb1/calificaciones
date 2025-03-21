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
        Schema::table('alumnos', function (Blueprint $table) {
            if (!Schema::hasColumn('alumnos', 'tutor_nombre')) {
                $table->string('tutor_nombre')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('alumnos', 'tutor_telefono')) {
                $table->string('tutor_telefono')->nullable()->after('tutor_nombre');
            }
            if (!Schema::hasColumn('alumnos', 'tutor_email')) {
                $table->string('tutor_email')->nullable()->after('tutor_telefono');
            }
            if (!Schema::hasColumn('alumnos', 'direccion')) {
                $table->text('direccion')->nullable()->after('tutor_email');
            }
            if (!Schema::hasColumn('alumnos', 'telefono_emergencia')) {
                $table->string('telefono_emergencia')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('alumnos', 'alergias')) {
                $table->text('alergias')->nullable()->after('telefono_emergencia');
            }
            if (!Schema::hasColumn('alumnos', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('alergias');
            }

            // Añadir el constraint CHECK para la columna 'genero' si no existe
            if (!Schema::hasColumn('alumnos', 'genero')) {
                $table->string('genero')->nullable();
            }

            // Verificar si el constraint CHECK ya existe en SQLite
            $checkExists = DB::selectOne("SELECT name FROM sqlite_master WHERE type='check' AND name='alumnos_genero_check'");

            if (!$checkExists) {
                // Añadir el constraint CHECK
                DB::statement('ALTER TABLE alumnos ADD CONSTRAINT alumnos_genero_check CHECK (genero IN ("masculino", "femenino", "otro"))');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn([
                'tutor_nombre',
                'tutor_telefono',
                'tutor_email',
                'direccion',
                'telefono_emergencia',
                'alergias',
                'observaciones',
            ]);
            // No es sencillo deshacer un constraint CHECK en SQLite de forma directa
            // Podrías optar por no hacer nada en el down para este constraint
            // o intentar una sentencia SQL para eliminarlo si es necesario y soportado.
        });
    }
};