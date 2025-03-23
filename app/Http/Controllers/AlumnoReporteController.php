<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\CampoFormativo;
use App\Models\EvaluacionDetalle;
use Illuminate\Http\Request;

class AlumnoReporteController extends Controller
{
    public function show($alumno_id)
    {
        $alumno = Alumno::with('grupo')->findOrFail($alumno_id);

        $asistencias = Asistencia::where('alumno_id', $alumno_id)->get();

        $totalDiasClase = 20; // Replace with actual logic to get total class days
        $totalAsistencias = $asistencias->where('estado_normalizado', 'asistio')->count();
        $totalFaltas = $asistencias->where('estado_normalizado', '!=', 'asistio')->count();
        $porcentajeAsistencia = ($totalDiasClase > 0) ? ($totalAsistencias / $totalDiasClase) * 100 : 0;

        $faltasPorCampoFormativo = [];
        $camposFormativos = CampoFormativo::all();
        $grupoId = $alumno->grupo_id;

        foreach ($camposFormativos as $campoFormativo) {
            $diasConCampo = DiaConCampoFormativo::where('grupo_id', $grupoId)
                ->where('campo_formativo_id', $campoFormativo->id)
                ->get();

            $totalFaltas = 0;
            foreach ($diasConCampo as $dia) {
                $falta = Asistencia::where('alumno_id', $alumno_id)
                    ->where('fecha', $dia->fecha)
                    ->where('estado_normalizado', 'falta')
                    ->count();
                $totalFaltas += $falta;
            }

            $faltasPorCampoFormativo[] = [
                'campo_formativo' => $campoFormativo->nombre,
                'total_faltas' => $totalFaltas,
            ];
        }

        $evaluacionDetalles = EvaluacionDetalle::where('alumno_id', $alumno_id)->get();

        $promedioGeneral = $evaluacionDetalles->avg('promedio_final');

        $rendimientoPorCampoFormativo = [];
        foreach ($camposFormativos as $campoFormativo) {
            $promedio = EvaluacionDetalle::where('alumno_id', $alumno_id)
                ->whereHas('evaluacion', function ($query) use ($campoFormativo) {
                    $query->where('campo_formativo_id', $campoFormativo->id);
                })
                ->avg('promedio_final');

            $rendimientoPorCampoFormativo[] = [
                'campo_formativo' => $campoFormativo->nombre,
                'promedio' => $promedio,
            ];
        }

        $datosIncompletos = [];
        if (empty($alumno->telefono_emergencia)) {
            $datosIncompletos[] = 'telefono_emergencia';
        }
        if (empty($alumno->alergias)) {
            $datosIncompletos[] = 'alergias';
        }
        if (empty($alumno->observaciones)) {
            $datosIncompletos[] = 'observaciones';
        }

        $reporte = [
            'alumno' => [
                'id' => $alumno->id,
                'nombre_completo' => $alumno->nombre_completo,
                'grupo' => $alumno->grupo->nombre ?? null,
                'curp' => $alumno->curp,
                'fecha_nacimiento' => $alumno->fecha_nacimiento,
                'genero' => $alumno->genero,
                'tutor_nombre' => $alumno->tutor_nombre,
                'tutor_telefono' => $alumno->tutor_telefono,
                'tutor_email' => $alumno->tutor_email,
                'direccion' => $alumno->direccion,
                'telefono_emergencia' => $alumno->telefono_emergencia,
                'alergias' => $alumno->alergias,
                'observaciones' => $alumno->observaciones,
            ],
            'asistencias' => [
                'total_dias_clase' => $totalDiasClase,
                'total_asistencias' => $totalAsistencias,
                'total_faltas' => $totalFaltas,
                'porcentaje_asistencia' => $porcentajeAsistencia,
                'faltas_por_campo_formativo' => $faltasPorCampoFormativo,
            ],
            'evaluaciones' => [
                'promedio_general' => $promedioGeneral,
                'rendimiento_por_campo_formativo' => $rendimientoPorCampoFormativo,
                'datos_incompletos' => $datosIncompletos,
            ],
        ];

        return response()->json($reporte);
    }
}
