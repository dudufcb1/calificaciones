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
        // Verificar que se está ejecutando el método
        \Log::info('====== MÉTODO exportarExcel INICIADO ======');
        \Log::info('Evaluación ID: ' . $this->evaluacionId . ' - Timestamp: ' . now()->toDateTimeString());

        try {
            // Notificar usuario para depuración visual
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Iniciando exportación a Excel...'
            ]);

            $evaluacion = Evaluacion::with('user')->findOrFail($this->evaluacionId);
            $currentUser = auth()->user();

            // Obtener el nombre del docente (usar el usuario actual si no hay asignado)
            $nombreDocente = $currentUser->name;
            if ($evaluacion->user) {
                $nombreDocente = $evaluacion->user->name;
            }

            \Log::info('Docente: ' . $nombreDocente . ' - Cantidad de detalles: ' . count($evaluacion->detalles));

            // Verificar si estamos en modo trial - mostrar diálogo SIEMPRE para usuarios trial
            $appTrialMode = env('APP_TRIAL_MODE', true);
            $userIsTrial = auth()->check() && auth()->user()->trial;
            $needsConfirmation = $appTrialMode && $userIsTrial; // Siempre mostrar para usuarios trial

            \Log::info('App Trial Mode: ' . ($appTrialMode ? 'Activo' : 'Inactivo') .
                      ' - Usuario Trial: ' . ($userIsTrial ? 'Sí' : 'No') .
                      ' - Requiere confirmación: ' . ($needsConfirmation ? 'Sí' : 'No'));

            if ($needsConfirmation) {
                // Usuario en modo trial, mostrar diálogo de confirmación SIEMPRE
                \Log::info('Intentando mostrar diálogo de confirmación para trial - ' . now()->toDateTimeString());

                // Despachar el evento para el diálogo de SweetAlert
                $this->dispatch('trial-excel-export');
                \Log::info('Evento trial-excel-export despachado correctamente');

                // Notificación visual adicional para depuración
                $this->dispatch('notify', [
                    'type' => 'info',
                    'message' => 'Por favor confirma la exportación limitada...'
                ]);

                return null; // Detener ejecución y esperar la confirmación del usuario
            }

            // Si no estamos en modo trial, exportar directamente sin diálogo
            \Log::info('Ejecutando exportación Excel directamente (sin diálogo) - ' . now()->toDateTimeString());

            // En modo trial limitamos a 10 registros, en modo normal exportamos todos
            $limitarRegistros = $appTrialMode && $userIsTrial;

            return $this->ejecutarExportacionExcel($evaluacion, $nombreDocente, $limitarRegistros);

        } catch (\Exception $e) {
            \Log::error('Error en exportarExcel: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al exportar: ' . $e->getMessage()
            ]);

            return null;
        }
    }

    #[On('confirmarExportarExcel')]
    public function confirmarExportarExcel()
    {
        \Log::info('====== MÉTODO confirmarExportarExcel INICIADO ======');
        \Log::info('Evaluación ID: ' . $this->evaluacionId . ' - Timestamp: ' . now()->toDateTimeString());

        try {
            // Notificar que se recibió la confirmación
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Procesando solicitud confirmada...'
            ]);

            $evaluacion = Evaluacion::with('user')->findOrFail($this->evaluacionId);
            $currentUser = auth()->user();

            // Obtener el nombre del docente
            $nombreDocente = $currentUser->name;
            if ($evaluacion->user) {
                $nombreDocente = $evaluacion->user->name;
            }

            \Log::info('Confirmación recibida para exportar en modo limitado - Docente: ' . $nombreDocente);

            // Exportar con límite de registros (usuario confirmó a través del SweetAlert)
            return $this->ejecutarExportacionExcel($evaluacion, $nombreDocente, true);

        } catch (\Exception $e) {
            \Log::error('Error en confirmarExportarExcel: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al procesar exportación confirmada: ' . $e->getMessage()
            ]);

            return null;
        }
    }

    private function ejecutarExportacionExcel($evaluacion, $nombreDocente, $limitarRegistros)
    {
        // Verificar si existe la plantilla usando separadores de ruta consistentes
        $templatePath = str_replace('/', DIRECTORY_SEPARATOR, storage_path('app/templates/evaluacion_template.xlsx'));
        $templateExists = file_exists($templatePath);
        
        \Log::info('Show Export Execute - Verificando plantilla en: ' . $templatePath);
        \Log::info('Show Export Execute - Plantilla existe: ' . ($templateExists ? 'SÍ' : 'NO'));

        if (!$templateExists) {
            \Log::warning('Show Export Execute - Plantilla no encontrada. Usando método sin plantilla.');
            
            // Crear una instancia de la clase Form para usar su método de exportación sin plantilla
            $formComponent = new \App\Livewire\Evaluacion\Form();
            
            try {
                // Llamar al método exportarExcelSinPlantilla pasando los parámetros necesarios
                $result = $formComponent->exportarExcelSinPlantilla($evaluacion, $nombreDocente, $limitarRegistros);
                
                \Log::info('Show Export Execute - Exportación sin plantilla ejecutada con éxito');
                
                if (!$result) {
                    \Log::error('Show Export Execute - El método sin plantilla devolvió NULL o false');
                    throw new \Exception('El método de exportación sin plantilla falló al generar el archivo');
                }
                
                return $result;
                
            } catch (\Exception $innerEx) {
                \Log::error('Show Export Execute - Error en exportación sin plantilla: ' . $innerEx->getMessage());
                \Log::error($innerEx->getTraceAsString());
                
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error al exportar sin plantilla: ' . $innerEx->getMessage()
                ]);
                
                return null;
            }
        }

        // Asegurar que el directorio temp existe
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            \Log::info('Creando directorio temporal: ' . $tempDir);
            mkdir($tempDir, 0755, true);
        }

        try {
            // Crear el archivo usando la plantilla
            \Log::info('Iniciando generación de archivo Excel');
            $export = new \App\Exports\EvaluacionExport($evaluacion, $templatePath, $nombreDocente, $limitarRegistros);
            $tempFile = $export->exportFromTemplate();
            \Log::info('Archivo generado en: ' . $tempFile);

            // Verificar si el archivo existe y tiene contenido
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('El archivo generado está vacío o no se pudo crear correctamente');
            }

            \Log::info('Preparando descarga del archivo Excel: ' . $tempFile);

            // Generar un nombre de archivo para la descarga
            $downloadFilename = 'evaluacion_' . $evaluacion->id . '.xlsx';

            // Redireccionar a una ruta especial que servirá el archivo
            // En lugar de devolver una respuesta directamente
            return response()->download($tempFile, $downloadFilename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Error en executeExportacionExcel: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al exportar: ' . $e->getMessage()
            ]);
            return null;
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

        // En modo trial, NO permitir exportar a PDF en absoluto
        // Solo mostrar mensaje informativo con SweetAlert
        if (env('APP_TRIAL_MODE', true)) {
            \Log::info('Exportación a PDF bloqueada en modo trial');
            $this->dispatch('trial-feature-disabled');
            return;
        }

        try {
            // Verificar si la evaluación está cargada correctamente
            \Log::info('ID de evaluación: ' . $evaluacion->id . ', Título: ' . $evaluacion->titulo);

            // Crear la exportación
            \Log::info('Iniciando proceso de exportación PDF');
            $export = new \App\Exports\EvaluacionPdfExport($evaluacion, $nombreDocente);

            // Verificar si la plantilla existe
            $view = 'exports.evaluacion-pdf';
            \Log::info('Verificando vista: ' . $view);
            if (!view()->exists($view)) {
                throw new \Exception("La vista '$view' no existe");
            }

            $pdf = $export->export();
            \Log::info('PDF generado correctamente');

            // Verificar que el output del PDF no esté vacío
            $output = $pdf->output();
            $size = strlen($output);
            \Log::info('Tamaño del PDF generado: ' . $size . ' bytes');

            if ($size <= 0) {
                throw new \Exception("El PDF generado está vacío");
            }

            // Generar un archivo temporal para el PDF
            $tempFile = storage_path('app/temp/evaluacion_' . $evaluacion->id . '_' . time() . '.pdf');
            file_put_contents($tempFile, $output);
            \Log::info('PDF guardado en archivo temporal: ' . $tempFile);

            // Devolver el archivo para descarga
            return response()->download($tempFile, 'evaluacion_' . $evaluacion->id . '.pdf', [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error en exportarPdf: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
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
