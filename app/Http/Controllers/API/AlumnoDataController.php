<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Evaluacion;
use App\Models\EvaluacionDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AlumnoDataController extends Controller
{
    /**
     * Obtiene los datos académicos completos de los alumnos asociados a un usuario
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAlumnosData(Request $request): JsonResponse
    {
        // Validar la solicitud
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'periodo_inicio' => 'nullable|date',
            'periodo_fin' => 'nullable|date|after_or_equal:periodo_inicio',
            'alumno_id' => 'nullable|exists:alumnos,id',
            'grupo_id' => 'nullable|exists:grupos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Obtener el usuario
        $user = User::findOrFail($request->user_id);

        // Query base para alumnos
        $alumnosQuery = Alumno::where('user_id', $user->id)
            ->with([
                'grupo',
                'user',
            ]);

        // Filtrar por alumno específico si se proporciona
        if ($request->has('alumno_id')) {
            $alumnosQuery->where('id', $request->alumno_id);
        }

        // Filtrar por grupo si se proporciona
        if ($request->has('grupo_id')) {
            $alumnosQuery->where('grupo_id', $request->grupo_id);
        }

        // Obtener los alumnos
        $alumnos = $alumnosQuery->get();

        // Definir el período para filtrar asistencias y evaluaciones
        $fechaInicio = $request->periodo_inicio ?? now()->startOfYear();
        $fechaFin = $request->periodo_fin ?? now();

        // Preparar respuesta con datos académicos completos
        $alumnosData = $alumnos->map(function ($alumno) use ($fechaInicio, $fechaFin) {
            // Obtener las asistencias del alumno
            $asistencias = Asistencia::where('alumno_id', $alumno->id)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get();

            // Cálculo de estadísticas de asistencia
            $totalAsistencias = $asistencias->count();
            $asistenciasPresente = $asistencias->where('estado_normalizado', 'asistio')->count();
            $asistenciasFalta = $asistencias->where('estado_normalizado', 'falta')->count();
            $asistenciasJustificadas = $asistencias->where('estado_normalizado', 'justificada')->count();

            // Obtener evaluaciones del alumno
            $evaluacionesDetalles = EvaluacionDetalle::where('alumno_id', $alumno->id)
                ->with(['evaluacion.campoFormativo', 'evaluacion.criterios', 'criterios'])
                ->whereHas('evaluacion', function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_evaluacion', [$fechaInicio, $fechaFin]);
                })
                ->get();

            // Cálculo de promedio general
            $promedioGeneral = $evaluacionesDetalles->avg('promedio_final') ?? 0;

            // Campos que pueden estar sin rellenar
            $camposSinRellenar = [];

            if (empty($alumno->curp)) {
                $camposSinRellenar[] = 'CURP';
            }

            if (empty($alumno->fecha_nacimiento)) {
                $camposSinRellenar[] = 'Fecha de nacimiento';
            }

            if (empty($alumno->tutor_nombre)) {
                $camposSinRellenar[] = 'Nombre del tutor';
            }

            if (empty($alumno->tutor_email)) {
                $camposSinRellenar[] = 'Email del tutor';
            }

            if (empty($alumno->telefono_tutor)) {
                $camposSinRellenar[] = 'Teléfono del tutor';
            }

            if (empty($alumno->telefono_emergencia)) {
                $camposSinRellenar[] = 'Teléfono de emergencia';
            }

            // Datos por campo formativo
            $evaluacionesPorCampo = collect();

            $evaluacionesDetalles->each(function ($detalle) use (&$evaluacionesPorCampo) {
                if ($detalle->evaluacion && $detalle->evaluacion->campoFormativo) {
                    $campoId = $detalle->evaluacion->campoFormativo->id;
                    $campoNombre = $detalle->evaluacion->campoFormativo->nombre;

                    if (!$evaluacionesPorCampo->has($campoId)) {
                        $evaluacionesPorCampo[$campoId] = [
                            'id' => $campoId,
                            'nombre' => $campoNombre,
                            'evaluaciones' => collect(),
                            'promedio' => 0,
                        ];
                    }

                    $evaluacionesPorCampo[$campoId]['evaluaciones']->push([
                        'id' => $detalle->evaluacion->id,
                        'titulo' => $detalle->evaluacion->titulo,
                        'fecha' => $detalle->evaluacion->fecha_evaluacion,
                        'calificacion' => $detalle->promedio_final,
                        'observaciones' => $detalle->observaciones,
                    ]);

                    // Recalcular promedio
                    $evaluacionesPorCampo[$campoId]['promedio'] =
                        $evaluacionesPorCampo[$campoId]['evaluaciones']->avg('calificacion');
                }
            });

            // Convertir a array de valores
            $camposFormativos = $evaluacionesPorCampo->values()->all();

            // Construir el objeto de respuesta para este alumno
            return [
                'id' => $alumno->id,
                'nombre' => $alumno->nombre,
                'apellido_paterno' => $alumno->apellido_paterno,
                'apellido_materno' => $alumno->apellido_materno,
                'nombre_completo' => $alumno->nombre_completo,
                'curp' => $alumno->curp,
                'fecha_nacimiento' => $alumno->fecha_nacimiento,
                'genero' => $alumno->genero,
                'grupo' => [
                    'id' => $alumno->grupo->id ?? null,
                    'nombre' => $alumno->grupo->nombre ?? null,
                ],
                'estado' => $alumno->estado,
                'tutor' => [
                    'nombre' => $alumno->tutor_nombre,
                    'email' => $alumno->tutor_email,
                    'telefono' => $alumno->telefono_tutor,
                ],
                'direccion' => $alumno->direccion,
                'telefono_emergencia' => $alumno->telefono_emergencia,
                'alergias' => $alumno->alergias,
                'observaciones' => $alumno->observaciones,
                'situacion_academica' => [
                    'promedio_general' => round($promedioGeneral, 2),
                    'campos_formativos' => $camposFormativos,
                ],
                'asistencias' => [
                    'total' => $totalAsistencias,
                    'presentes' => $asistenciasPresente,
                    'faltas' => $asistenciasFalta,
                    'justificadas' => $asistenciasJustificadas,
                    'porcentaje_asistencia' => $totalAsistencias > 0
                        ? round(($asistenciasPresente + $asistenciasJustificadas) * 100 / $totalAsistencias, 2)
                        : 0,
                    'detalle' => $asistencias->map(function ($asistencia) {
                        return [
                            'fecha' => $asistencia->fecha,
                            'estado' => $asistencia->estado_normalizado,
                            'justificacion' => $asistencia->justificacion,
                            'observaciones' => $asistencia->observaciones,
                        ];
                    }),
                ],
                'campos_sin_rellenar' => $camposSinRellenar,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin,
                ],
                'alumnos' => $alumnosData,
            ]
        ]);
    }
}
