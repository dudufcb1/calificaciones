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
        // Verifica si la tabla alumnos_old existe y la elimina si es el caso
        if (Schema::hasTable('alumnos_old')) {
            Schema::drop('alumnos_old');
        }

        // Verifica si la tabla alumnos existe antes de intentar renombrarla
        if (Schema::hasTable('alumnos')) {
            // Rename the alumnos table
            Schema::rename('alumnos', 'alumnos_old');
        }

        // Create the new alumnos table WITH the CHECK constraint using raw SQL
        \Illuminate\Support\Facades\DB::statement("
            CREATE TABLE alumnos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre VARCHAR(255) NOT NULL,
                apellido_paterno VARCHAR(255) NOT NULL,
                apellido_materno VARCHAR(255) NOT NULL,
                grupo_id INTEGER NULL,
                curp VARCHAR(255) NULL UNIQUE,
                fecha_nacimiento DATE NULL,
                genero VARCHAR(255) NULL CHECK (genero IN ('masculino', 'femenino', 'otro')),
                estado VARCHAR(255) DEFAULT 'activo',
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                deleted_at DATETIME NULL,
                user_id INTEGER NULL,
                tutor_nombre VARCHAR(255) NULL,
                tutor_telefono VARCHAR(255) NULL,
                tutor_email VARCHAR(255) NULL,
                direccion TEXT NULL,
                telefono_emergencia VARCHAR(255) NULL,
                alergias TEXT NULL,
                observaciones TEXT NULL,
                FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // Copy data from the old table to the new table if it existed
        if (Schema::hasTable('alumnos_old')) {
            \Illuminate\Support\Facades\DB::statement('INSERT INTO alumnos (id, user_id, nombre, apellido_paterno, apellido_materno, grupo_id, curp, fecha_nacimiento, genero, estado, created_at, updated_at, deleted_at, tutor_nombre, tutor_telefono, tutor_email, direccion, telefono_emergencia, alergias, observaciones) SELECT id, user_id, nombre, apellido_paterno, apellido_materno, grupo_id, curp, fecha_nacimiento, genero, estado, created_at, updated_at, deleted_at, tutor_nombre, tutor_telefono, tutor_email, direccion, telefono_emergencia, alergias, observaciones FROM alumnos_old');

            // Drop the old table if it existed
            Schema::drop('alumnos_old');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
        if (Schema::hasTable('alumnos_old')) {
            Schema::rename('alumnos_old', 'alumnos');
        }
    }
};