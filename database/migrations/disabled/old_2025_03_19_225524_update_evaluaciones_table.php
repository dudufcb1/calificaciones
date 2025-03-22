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
        // SQLite no soporta DROP COLUMN directamente, así que debemos recrear la tabla
        // Primero, crear una tabla temporal con la nueva estructura
        Schema::create('evaluaciones_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_formativo_id')->constrained('campo_formativos')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_evaluacion')->nullable();
            $table->boolean('is_draft')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Transferir los datos de la tabla original a la temporal
        $evaluaciones = DB::table('evaluaciones')->get();
        foreach ($evaluaciones as $evaluacion) {
            DB::table('evaluaciones_temp')->insert([
                'id' => $evaluacion->id,
                'campo_formativo_id' => $evaluacion->campo_formativo_id,
                'titulo' => 'Evaluación #' . $evaluacion->id,
                'is_draft' => $evaluacion->is_draft,
                'created_at' => $evaluacion->created_at,
                'updated_at' => $evaluacion->updated_at,
                'deleted_at' => $evaluacion->deleted_at
            ]);
        }

        // Eliminar la tabla original
        Schema::drop('evaluaciones');

        // Renombrar la tabla temporal a la original
        Schema::rename('evaluaciones_temp', 'evaluaciones');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Similar al up, pero invertido
        Schema::create('evaluaciones_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_formativo_id')->constrained('campo_formativos')->onDelete('cascade');
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->decimal('promedio_final', 5, 2)->default(0);
            $table->boolean('is_draft')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Transferir los datos (sin alumno_id y promedio_final, ya que no existen en la nueva estructura)
        $evaluaciones = DB::table('evaluaciones')->get();
        foreach ($evaluaciones as $evaluacion) {
            // No podemos transferir los datos correctamente,
            // así que solo transferimos los IDs para mantener las relaciones
            DB::table('evaluaciones_temp')->insert([
                'id' => $evaluacion->id,
                'campo_formativo_id' => $evaluacion->campo_formativo_id,
                'alumno_id' => 1, // Valor por defecto
                'promedio_final' => 0,
                'is_draft' => $evaluacion->is_draft,
                'created_at' => $evaluacion->created_at,
                'updated_at' => $evaluacion->updated_at,
                'deleted_at' => $evaluacion->deleted_at
            ]);
        }

        Schema::drop('evaluaciones');
        Schema::rename('evaluaciones_temp', 'evaluaciones');
    }
};
