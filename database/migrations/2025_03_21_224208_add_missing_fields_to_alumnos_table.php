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
        });

        // Rename the alumnos table
        Schema::rename('alumnos', 'alumnos_old');

        // Create the new alumnos table with the CHECK constraint
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->string('curp')->nullable()->unique('alumnos_curp_unique');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('estado')->default('activo');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            $table->string('tutor_nombre')->nullable()->after('user_id');
            $table->string('tutor_telefono')->nullable()->after('tutor_nombre');
            $table->string('tutor_email')->nullable()->after('tutor_telefono');
            $table->text('direccion')->nullable()->after('tutor_email');
            $table->string('telefono_emergencia')->nullable()->after('direccion');
            $table->text('alergias')->nullable()->after('telefono_emergencia');
            $table->text('observaciones')->nullable()->after('alergias');

            $table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->check('genero IN ("masculino", "femenino", "otro")');
        });

        // Copy data from the old table to the new table
        DB::statement('INSERT INTO alumnos (id, user_id, nombre, apellido_paterno, apellido_materno, grupo_id, curp, fecha_nacimiento, genero, estado, created_at, updated_at, deleted_at, tutor_nombre, tutor_telefono, tutor_email, direccion, telefono_emergencia, alergias, observaciones) SELECT id, user_id, nombre, apellido_paterno, apellido_materno, grupo_id, curp, fecha_nacimiento, genero, estado, created_at, updated_at, deleted_at, tutor_nombre, tutor_telefono, tutor_email, direccion, telefono_emergencia, alergias, observaciones FROM alumnos_old');

        // Drop the old table
        Schema::drop('alumnos_old');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
        Schema::rename('alumnos_old', 'alumnos');
    }
};