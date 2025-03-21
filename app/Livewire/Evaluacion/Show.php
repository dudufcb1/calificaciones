<?php

namespace App\Livewire\Evaluacion;

use App\Models\Evaluacion;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Exports\EvaluacionExport;
use App\Exports\EvaluacionPdfExport;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class Show extends Component
{
    public $evaluacionId;
    public $evaluacion;
    public $detalles = [];
    public $criterios = [];

    public function mount($evaluacionId)
    {
        $this->evaluacionId = $evaluacionId;
        $this->loadEvaluacion();
    }

    public function loadEvaluacion()
    {
        $this->evaluacion = Evaluacion::with(['campoFormativo', 'detalles.alumno', 'detalles.criterios', 'user'])
            ->findOrFail($this->evaluacionId);

        $this->criterios = $this->evaluacion->campoFormativo->criterios()->orderBy('orden')->get()->toArray();

        // Preparar los detalles en un formato conveniente para mostrar
        foreach ($this->evaluacion->detalles as $detalle) {
            $calificaciones = [];
            $sumaPonderada = 0;
            $sumaPesos = 0;

            foreach ($this->criterios as $criterio) {
                $calificacionCriterio = $detalle->criterios->firstWhere('id', $criterio['id']);
                $valor = $calificacionCriterio ? $calificacionCriterio->pivot->calificacion : 0;
                $ponderada = $calificacionCriterio ? $calificacionCriterio->pivot->calificacion_ponderada : 0;

                // Si la ponderada no está calculada correctamente, calcularla
                if ($ponderada == 0 && $valor > 0) {
                    $ponderada = $valor * ($criterio['porcentaje'] / 100);
                }

                $calificaciones[] = [
                    'criterio_id' => $criterio['id'],
                    'valor' => $valor,
                    'ponderada' => $ponderada,
                ];

                $sumaPonderada += $ponderada;
                $sumaPesos += $criterio['porcentaje'] / 100;
            }

            // Recalcular el promedio basado en las calificaciones ponderadas
            $promedio = $sumaPesos > 0 ? round($sumaPonderada / $sumaPesos, 2) : 0;

            $this->detalles[] = [
                'id' => $detalle->id,
                'alumno_id' => $detalle->alumno_id,
                'nombre' => $detalle->alumno->nombre_completo,
                'calificaciones' => $calificaciones,
                'promedio' => $promedio,
                'observaciones' => $detalle->observaciones
            ];
        }
    }

    public function actualizarPromediosEnBD()
    {
        // Recorrer cada detalle y actualizar su promedio en la base de datos
        foreach ($this->detalles as $detalle) {
            $detalleModel = \App\Models\EvaluacionDetalle::find($detalle['id']);
            if ($detalleModel) {
                $detalleModel->promedio_final = $detalle['promedio'];
                $detalleModel->save();

                // Actualizar también las calificaciones ponderadas
                foreach ($detalle['calificaciones'] as $calificacion) {
                    $detalleModel->criterios()->updateExistingPivot(
                        $calificacion['criterio_id'],
                        ['calificacion_ponderada' => $calificacion['ponderada']]
                    );
                }
            }
        }

        // En lugar de recargar todos los datos, solo notificamos al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Promedios actualizados correctamente'
        ]);
    }

    public function exportarExcel()
    {
        $evaluacion = Evaluacion::with('user')->findOrFail($this->evaluacionId);
        $currentUser = auth()->user();

        // Obtener el nombre del docente (usar el usuario actual si no hay asignado)
        $nombreDocente = $currentUser->name;
        if ($evaluacion->user) {
            $nombreDocente = $evaluacion->user->name;
        }

        \Log::info('Exportando evaluación. Docente: ' . $nombreDocente);

        // Comprobar si el usuario está en modo trial
        if ($currentUser->trial) {
            // En lugar de exportar directamente, mostrar alerta de confirmación
            $this->dispatch('trial-excel-export');
            return;
        }

        // Si no está en modo trial, exportar normalmente
        $this->ejecutarExportacionExcel($evaluacion, $nombreDocente, false);
    }

    #[On('confirmarExportarExcel')]
    public function confirmarExportarExcel()
    {
        $evaluacion = Evaluacion::with('user')->findOrFail($this->evaluacionId);
        $currentUser = auth()->user();

        // Obtener el nombre del docente
        $nombreDocente = $currentUser->name;
        if ($evaluacion->user) {
            $nombreDocente = $evaluacion->user->name;
        }

        // Exportar con límite de registros (usuario confirmó a través del SweetAlert)
        $this->ejecutarExportacionExcel($evaluacion, $nombreDocente, true);
    }

    private function ejecutarExportacionExcel($evaluacion, $nombreDocente, $limitarRegistros)
    {
        // Verificar si existe la plantilla
        $templatePath = storage_path('app/templates/evaluacion_template.xlsx');

        if (!file_exists($templatePath)) {
            // Si no existe la plantilla, crearemos un archivo normal
            return Excel::download(
                new EvaluacionExport($evaluacion, null, $nombreDocente, $limitarRegistros),
                'evaluacion_' . $evaluacion->id . '.xlsx'
            );
        }

        try {
            // Crear el archivo usando la plantilla
            $export = new EvaluacionExport($evaluacion, $templatePath, $nombreDocente, $limitarRegistros);
            $tempFile = $export->exportFromTemplate();

            // Preparar la respuesta para descargar
            return response()->download($tempFile, 'evaluacion_' . $evaluacion->id . '.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al exportar: ' . $e->getMessage()
            ]);
        }
    }

    public function exportarPdf()
    {
        $evaluacion = Evaluacion::with('user')->findOrFail($this->evaluacionId);
        $currentUser = auth()->user();

        // Obtener el nombre del docente (usar el usuario actual si no hay asignado)
        $nombreDocente = $currentUser->name;
        if ($evaluacion->user) {
            $nombreDocente = $evaluacion->user->name;
        }

        \Log::info('Exportando evaluación a PDF. Docente: ' . $nombreDocente);

        // Comprobar si el usuario está en modo trial
        if ($currentUser->trial) {
            $this->dispatch('trial-feature-disabled');
            return;
        }

        try {
            // Crear la exportación
            $export = new EvaluacionPdfExport($evaluacion, $nombreDocente);
            $pdf = $export->export();

            return response()->streamDownload(
                fn () => print($pdf->output()),
                'evaluacion_' . $evaluacion->id . '.pdf'
            );
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al exportar a PDF: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.evaluacion.show');
    }
}
