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
        // Desactivar temporalmente la salida de errores para evitar corromper la respuesta JSON
        $previousErrorReporting = error_reporting();
        error_reporting(E_ERROR); // Solo reportar errores fatales
        
        try {
            // 1. Notificar al usuario que estamos procesando
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Generando PDF, por favor espere...'
            ]);
            
            // 2. Cargar la evaluación con sus relaciones
            $evaluacion = Evaluacion::with('user')->findOrFail($this->evaluacionId);
            $currentUser = auth()->user();

            // 3. Verificar permisos trial
            $trialMode = env('APP_TRIAL_MODE', true);
            $userIsTrial = $currentUser->trial ?? true;
            
            \Log::info('PDF Export - Trial mode: ' . ($trialMode ? 'true' : 'false'));
            \Log::info('PDF Export - User is trial: ' . ($userIsTrial ? 'true' : 'false'));
            
            if ($trialMode && $userIsTrial) {
                \Log::info('Exportación a PDF bloqueada en modo trial - Usuario trial');
                $this->dispatch('trial-feature-disabled');
                return;
            }
            
            // 4. Generar un nombre único para el archivo
            $tempFilename = 'evaluacion_' . $evaluacion->id . '_' . time() . '.pdf';
            
            // 5. Crear una sesión temporal con los datos necesarios para el controlador
            session()->put('pdf_export', [
                'evaluacion_id' => $evaluacion->id,
                'temp_filename' => $tempFilename,
                'docente' => $currentUser->name,
                'expires_at' => now()->addMinutes(5)
            ]);
            
            // 6. Generar URL de redirección al controlador
            $downloadUrl = route('evaluaciones.pdf.download', [
                'id' => $evaluacion->id,
                'token' => csrf_token()
            ]);
            
            // 7. Notificar éxito y mostrar enlace de descarga
            $this->dispatch('swal', [
                'title' => 'PDF Listo para Descargar',
                'html' => 'Haga clic en el botón para descargar el PDF.<br><br>' .
                        '<a href="' . $downloadUrl . '" target="_blank" ' .
                        'class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent ' .
                        'rounded-md font-semibold text-xs text-white uppercase tracking-widest ' .
                        'hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 ' .
                        'focus:ring focus:ring-blue-300 disabled:opacity-25 transition">' .
                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" ' .
                        'viewBox="0 0 24 24" stroke="currentColor">' .
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" ' .
                        'd="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />' .
                        '</svg>Descargar PDF</a>',
                'icon' => 'success',
                'showConfirmButton' => false,
                'showCloseButton' => true,
            ]);
            
            \Log::info('PDF Export - Proceso completado, redirigido a: ' . $downloadUrl);
            
        } catch (\Exception $e) {
            \Log::error('Error en exportarPdf: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al exportar a PDF: ' . $e->getMessage()
            ]);
        } finally {
            // Restaurar la configuración de reporte de errores
            error_reporting($previousErrorReporting);
        }
    }

    /**
     * Emite un evento al navegador para iniciar la descarga de un archivo
     */
    private function dispatchBrowser($event, $url)
    {
        \Log::info("Despachando evento de descarga: {$event} con URL: {$url}");
        
        // Despachar evento Livewire (para listeners registrados con Livewire.on)
        $this->dispatch($event, $url);
        
        // Despachar evento nativo de browser (para listeners registrados con window.addEventListener)
        $this->dispatch('browser-event', [
            'name' => $event,
            'data' => $url
        ]);
        
        return null;
    }

    public function render()
    {
        return view('livewire.evaluacion.show');
    }
}
