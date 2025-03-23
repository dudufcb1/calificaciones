<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\CampoFormativo;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AsistenciaService
{
    /**
     * Calcula las estadísticas generales de asistencia para un alumno o grupo en un período específico
     *
     * @param int|array $alumnoIds ID o array de IDs de alumnos
     * @param string $fechaInicio Fecha de inicio en formato Y-m-d
     * @param string $fechaFin Fecha de fin en formato Y-m-d
     * @param array $diasNoLaborables Array de fechas no laborables en formato Y-m-d
     * @return array Estadísticas de asistencia
     */
    public function calcularEstadisticas($alumnoIds, string $fechaInicio, string $fechaFin, array $diasNoLaborables = [])
    {
        // Convertir a array si es un solo ID
        if (!is_array($alumnoIds)) {
            $alumnoIds = [$alumnoIds];
        }

        // Obtener todos los alumnos involucrados
        $alumnos = Alumno::whereIn('id', $alumnoIds)->get();

        // Generar todas las fechas del período
        $fechaActual = Carbon::parse($fechaInicio);
        $fechaFinal = Carbon::parse($fechaFin);
        $diasDelPeriodo = [];

        while ($fechaActual->lte($fechaFinal)) {
            $fechaStr = $fechaActual->format('Y-m-d');
            $diasDelPeriodo[] = [
                'fecha' => $fechaStr,
                'es_fin_semana' => $fechaActual->isWeekend()
            ];
            $fechaActual->addDay();
        }

        // Si no se proporcionaron días no laborables, considerar fines de semana como no laborables
        if (empty($diasNoLaborables)) {
            $diasNoLaborables = collect($diasDelPeriodo)
                ->filter(function($dia) {
                    return $dia['es_fin_semana'];
                })
                ->pluck('fecha')
                ->toArray();
        }

        // Obtener todas las asistencias del período para los alumnos
        $asistenciasDB = Asistencia::whereIn('alumno_id', $alumnoIds)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        // Inicializar el array de resultados
        $resultados = [];

        // Calcular estadísticas para cada alumno
        foreach ($alumnos as $alumno) {
            $asistencias = [];

            // Preparar datos de asistencia para este alumno
            foreach ($diasDelPeriodo as $dia) {
                $fecha = $dia['fecha'];

                // Buscar asistencia para este alumno y fecha
                $asistencia = $asistenciasDB->first(function ($item) use ($alumno, $fecha) {
                    return $item->alumno_id == $alumno->id && $item->fecha->format('Y-m-d') == $fecha;
                });

                $estado = 'falta'; // Estado por defecto

                if ($asistencia) {
                    $estado = $asistencia->estado_normalizado ?? $asistencia->estado;
                }

                $asistencias[$fecha] = $estado;
            }

            // Calcular estadísticas
            $totalDias = 0;
            $totalAsistencias = 0;
            $totalInasistencias = 0;
            $totalJustificadas = 0;

            foreach ($diasDelPeriodo as $dia) {
                $fecha = $dia['fecha'];

                // No contar días no laborables
                if (in_array($fecha, $diasNoLaborables)) {
                    continue;
                }

                $totalDias++;

                $estado = $asistencias[$fecha] ?? 'falta';

                if ($estado == 'asistio') {
                    $totalAsistencias++;
                } elseif ($estado == 'justificada') {
                    $totalJustificadas++;
                } else {
                    $totalInasistencias++;
                }
            }

            // Calcular porcentajes (evitar división por cero)
            $porcentajeAsistencia = $totalDias > 0 ? round(($totalAsistencias / $totalDias) * 100, 2) : 0;
            $porcentajeInasistencia = $totalDias > 0 ? round(($totalInasistencias / $totalDias) * 100, 2) : 0;

            // Guardar resultados
            $resultados[$alumno->id] = [
                'alumno' => $alumno,
                'total_dias' => $totalDias,
                'asistencias' => $totalAsistencias,
                'inasistencias' => $totalInasistencias,
                'justificadas' => $totalJustificadas,
                'porcentaje_asistencia' => $porcentajeAsistencia,
                'porcentaje_inasistencia' => $porcentajeInasistencia,
            ];
        }

        return $resultados;
    }

    /**
     * Calcula las estadísticas de asistencia por campo formativo
     *
     * @param int|array $alumnoIds ID o array de IDs de alumnos
     * @param string $fechaInicio Fecha de inicio en formato Y-m-d
     * @param string $fechaFin Fecha de fin en formato Y-m-d
     * @param array $diasNoLaborables Array de fechas no laborables en formato Y-m-d
     * @param array $camposFormativosPorDia Array asociativo [fecha => [campo_formativo_ids]]
     * @return array Estadísticas por campo formativo
     */
    public function calcularEstadisticasPorCampoFormativo(
        $alumnoIds,
        string $fechaInicio,
        string $fechaFin,
        array $diasNoLaborables = [],
        array $camposFormativosPorDia = []
    ) {
        // Convertir a array si es un solo ID
        if (!is_array($alumnoIds)) {
            $alumnoIds = [$alumnoIds];
        }

        // Obtener alumnos y campos formativos
        $alumnos = Alumno::whereIn('id', $alumnoIds)->get();
        $camposFormativos = CampoFormativo::all();

        // Si no se proporcionó la asignación de campos formativos por día, obtenerla de la base de datos
        if (empty($camposFormativosPorDia)) {
            // Obtener todos los días con campos formativos para este período
            $diasConCampos = \App\Models\DiaConCampoFormativo::whereIn('grupo_id', function($query) use ($alumnoIds) {
                $query->select('grupo_id')
                    ->from('alumnos')
                    ->whereIn('id', $alumnoIds)
                    ->distinct();
            })
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

            // Agrupar por fecha
            foreach ($diasConCampos as $diaConCampo) {
                $fecha = $diaConCampo->fecha->format('Y-m-d');

                if (!isset($camposFormativosPorDia[$fecha])) {
                    $camposFormativosPorDia[$fecha] = [];
                }

                $camposFormativosPorDia[$fecha][] = $diaConCampo->campo_formativo_id;
            }
        }

        // Generar todas las fechas del período
        $fechaActual = Carbon::parse($fechaInicio);
        $fechaFinal = Carbon::parse($fechaFin);
        $diasDelPeriodo = [];

        while ($fechaActual->lte($fechaFinal)) {
            $fechaStr = $fechaActual->format('Y-m-d');
            $diasDelPeriodo[] = [
                'fecha' => $fechaStr,
                'es_fin_semana' => $fechaActual->isWeekend()
            ];
            $fechaActual->addDay();
        }

        // Si no se proporcionaron días no laborables, considerar fines de semana como no laborables
        if (empty($diasNoLaborables)) {
            $diasNoLaborables = collect($diasDelPeriodo)
                ->filter(function($dia) {
                    return $dia['es_fin_semana'];
                })
                ->pluck('fecha')
                ->toArray();
        }

        // Obtener asistencias para el período
        $asistenciasDB = Asistencia::whereIn('alumno_id', $alumnoIds)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        // Preparar asistencias por alumno
        $asistenciasPorAlumno = [];
        foreach ($alumnos as $alumno) {
            $asistenciasPorAlumno[$alumno->id] = [];

            foreach ($diasDelPeriodo as $dia) {
                $fecha = $dia['fecha'];

                $asistencia = $asistenciasDB->first(function ($item) use ($alumno, $fecha) {
                    return $item->alumno_id == $alumno->id && $item->fecha->format('Y-m-d') == $fecha;
                });

                $estado = 'falta'; // Estado por defecto

                if ($asistencia) {
                    $estado = $asistencia->estado_normalizado ?? $asistencia->estado;
                }

                $asistenciasPorAlumno[$alumno->id][$fecha] = $estado;
            }
        }

        // Inicializar estadísticas por campo formativo
        $estadisticasPorCampoFormativo = [];

        foreach ($alumnos as $alumno) {
            $estadisticasPorCampoFormativo[$alumno->id] = [];

            foreach ($camposFormativos as $campo) {
                $estadisticasPorCampoFormativo[$alumno->id][$campo->id] = [
                    'campo' => $campo,
                    'total_dias' => 0,
                    'asistencias' => 0,
                    'inasistencias' => 0,
                    'justificadas' => 0,
                    'porcentaje_asistencia' => 0,
                    'porcentaje_inasistencia' => 0,
                ];
            }
        }

        // Recorrer todos los días del período
        foreach ($diasDelPeriodo as $dia) {
            $fecha = $dia['fecha'];

            // No contar días no laborables
            if (in_array($fecha, $diasNoLaborables)) {
                continue;
            }

            // Obtener campos formativos para este día
            $camposFormativosDia = $camposFormativosPorDia[$fecha] ?? [];

            // Si no hay campos formativos para este día, continuar
            if (empty($camposFormativosDia)) {
                continue;
            }

            // Calcular estadísticas para cada alumno y campo formativo
            foreach ($alumnos as $alumno) {
                $estado = $asistenciasPorAlumno[$alumno->id][$fecha] ?? 'falta';

                foreach ($camposFormativosDia as $campoFormativoId) {
                    // Incrementar el contador total_dias
                    $estadisticasPorCampoFormativo[$alumno->id][$campoFormativoId]['total_dias']++;

                    // Incrementar el contador correspondiente según el estado
                    if ($estado == 'asistio') {
                        $estadisticasPorCampoFormativo[$alumno->id][$campoFormativoId]['asistencias']++;
                    } elseif ($estado == 'justificada') {
                        $estadisticasPorCampoFormativo[$alumno->id][$campoFormativoId]['justificadas']++;
                    } else {
                        $estadisticasPorCampoFormativo[$alumno->id][$campoFormativoId]['inasistencias']++;
                    }
                }
            }
        }

        // Calcular porcentajes
        foreach ($alumnos as $alumno) {
            foreach ($camposFormativos as $campo) {
                $totalDias = $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['total_dias'];
                $asistencias = $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['asistencias'];
                $inasistencias = $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['inasistencias'];

                // Evitar división por cero
                $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['porcentaje_asistencia'] =
                    $totalDias > 0 ? round(($asistencias / $totalDias) * 100, 2) : 0;

                $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['porcentaje_inasistencia'] =
                    $totalDias > 0 ? round(($inasistencias / $totalDias) * 100, 2) : 0;
            }
        }

        return $estadisticasPorCampoFormativo;
    }

    /**
     * Método conveniente para obtener estadísticas para un mes específico
     *
     * @param int|array $alumnoIds ID o array de IDs de alumnos
     * @param int $mes Número de mes (1-12)
     * @param int $anio Año
     * @param array $diasNoLaborables Array de fechas no laborables
     * @return array Estadísticas del mes
     */
    public function obtenerEstadisticasMensuales($alumnoIds, int $mes, int $anio, array $diasNoLaborables = [])
    {
        $fechaInicio = Carbon::createFromDate($anio, $mes, 1)->format('Y-m-d');
        $fechaFin = Carbon::createFromDate($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

        return $this->calcularEstadisticas($alumnoIds, $fechaInicio, $fechaFin, $diasNoLaborables);
    }

    /**
     * Método conveniente para obtener estadísticas por campo formativo para un mes específico
     *
     * @param int|array $alumnoIds ID o array de IDs de alumnos
     * @param int $mes Número de mes (1-12)
     * @param int $anio Año
     * @param array $diasNoLaborables Array de fechas no laborables
     * @param array $camposFormativosPorDia Array asociativo [fecha => [campo_formativo_ids]]
     * @return array Estadísticas por campo formativo del mes
     */
    public function obtenerEstadisticasPorCampoFormativoMensuales(
        $alumnoIds,
        int $mes,
        int $anio,
        array $diasNoLaborables = [],
        array $camposFormativosPorDia = []
    ) {
        $fechaInicio = Carbon::createFromDate($anio, $mes, 1)->format('Y-m-d');
        $fechaFin = Carbon::createFromDate($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

        return $this->calcularEstadisticasPorCampoFormativo(
            $alumnoIds,
            $fechaInicio,
            $fechaFin,
            $diasNoLaborables,
            $camposFormativosPorDia
        );
    }
}
