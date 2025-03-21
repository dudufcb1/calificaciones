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
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $columns = $sm->listTableColumns('alumnos');
            $hasGeneroCheck = false;
            if (isset($columns['genero'])) {
                $tableDetails = $sm->listTableDetails('alumnos');
                foreach ($tableDetails->getChecks() as $check) {
                    if (str_contains(strtolower($check->getExpression()), strtolower("genero") . " in ")) {
                        $hasGeneroCheck = true;
                        break;
                    }
                }
                if (!$hasGeneroCheck) {
                    $table->string('genero')->nullable()->change(); // Hacer la columna nullable temporalmente para añadir el constraint
                    DB::statement('ALTER TABLE alumnos ADD CONSTRAINT alumnos_genero_check CHECK (genero IN ("masculino", "femenino", "otro"))');
                    $table->string('genero')->nullable(false)->change(); // Volver a hacerlo not null si era antes
                }
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
        });
    }
};