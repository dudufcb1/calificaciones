<?php

namespace App\Exports;

use App\Models\Evaluacion;
use Illuminate\Contracts\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class EvaluacionPdfExport
{
    protected $evaluacion;
    protected $nombreDocente;
    protected $limitarRegistros;

    public function __construct(Evaluacion $evaluacion, $nombreDocente, $limitarRegistros = false)
    {
        $this->evaluacion = $evaluacion;
        $this->nombreDocente = $nombreDocente;
        $this->limitarRegistros = $limitarRegistros;
    }

    public function export()
    {
        $evaluacion = $this->evaluacion->load(['campoFormativo', 'detalles.alumno', 'detalles.criterios']);
        $criterios = $evaluacion->campoFormativo->criterios()->orderBy('orden')->get();

        // Preparar los datos de detalles para la vista PDF
        $detalles = [];
        foreach ($evaluacion->detalles as $index => $detalle) {
            // Si estamos en modo trial y ya procesamos 10 registros, salimos del bucle
            if ($this->limitarRegistros && $index >= 10) {
                break;
            }

            $calificaciones = [];
            $sumaPonderada = 0;
            $sumaPesos = 0;

            foreach ($criterios as $criterio) {
                $calificacionCriterio = $detalle->criterios->firstWhere('id', $criterio->id);
                $valor = $calificacionCriterio ? $calificacionCriterio->pivot->calificacion : 0;
                $ponderada = $calificacionCriterio ? $calificacionCriterio->pivot->calificacion_ponderada : 0;

                // Si la ponderada no está calculada correctamente, calcularla
                if ($ponderada == 0 && $valor > 0) {
                    $ponderada = $valor * ($criterio->porcentaje / 100);
                }

                $calificaciones[] = [
                    'criterio_id' => $criterio->id,
                    'valor' => $valor,
                    'ponderada' => $ponderada,
                ];

                $sumaPonderada += $ponderada;
                $sumaPesos += $criterio->porcentaje / 100;
            }

            // Recalcular el promedio
            $promedio = $sumaPesos > 0 ? round($sumaPonderada / $sumaPesos, 2) : 0;

            $detalles[] = [
                'id' => $detalle->id,
                'alumno_id' => $detalle->alumno_id,
                'nombre' => $detalle->alumno->nombre_completo,
                'calificaciones' => $calificaciones,
                'promedio' => $promedio,
                'observaciones' => $detalle->observaciones
            ];
        }

        // Generar el PDF
        $data = [
            'evaluacion' => $evaluacion,
            'criterios' => $criterios,
            'detalles' => $detalles,
            'nombreDocente' => $this->nombreDocente,
            'limitarRegistros' => $this->limitarRegistros,
            'fecha' => now()->format('d/m/Y'),
        ];

        $pdf = PDF::loadView('exports.evaluacion-pdf', $data);

        return $pdf;
    }
}
