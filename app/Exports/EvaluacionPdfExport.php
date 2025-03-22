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
        try {
            \Log::info('Iniciando export en EvaluacionPdfExport');

            $evaluacion = $this->evaluacion->load(['campoFormativo', 'detalles.alumno', 'detalles.criterios']);
            \Log::info('Evaluación cargada con relaciones. ID: ' . $evaluacion->id);

            $criterios = $evaluacion->campoFormativo->criterios()->orderBy('orden')->get();
            \Log::info('Criterios cargados: ' . $criterios->count());

            // Preparar los datos de detalles para la vista PDF
            $detalles = [];
            \Log::info('Total detalles a procesar: ' . $evaluacion->detalles->count());

            foreach ($evaluacion->detalles as $index => $detalle) {
                // Si estamos en modo trial y ya procesamos 10 registros, salimos del bucle
                if ($this->limitarRegistros && $index >= 10) {
                    \Log::info('Limitando a 10 registros (modo trial)');
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

            \Log::info('Detalles procesados: ' . count($detalles));

            // Generar el PDF
            $data = [
                'evaluacion' => $evaluacion,
                'criterios' => $criterios,
                'detalles' => $detalles,
                'nombreDocente' => $this->nombreDocente,
                'limitarRegistros' => $this->limitarRegistros,
                'fecha' => now()->format('d/m/Y'),
            ];

            \Log::info('Cargando vista para PDF');
            $pdf = PDF::loadView('exports.evaluacion-pdf', $data);

            // Configurar PDF
            $pdf->setPaper('a4', 'landscape');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'debugCss' => true
            ]);

            \Log::info('PDF cargado correctamente');
            return $pdf;
        } catch (\Exception $e) {
            \Log::error('Error en EvaluacionPdfExport::export: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
