<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluacion;
use App\Exports\EvaluacionExport;

class EvaluacionController extends Controller
{
    /**
     * Exportar evaluación a Excel
     */
    public function exportarExcel($evaluacionId)
    {
        try {
            \Log::info('Exportación Excel iniciada desde controlador directo. ID: ' . $evaluacionId);

            $evaluacion = Evaluacion::with('user')->findOrFail($evaluacionId);
            $currentUser = auth()->user();

            // Obtener el nombre del docente (usar el usuario actual si no hay asignado)
            $nombreDocente = $currentUser->name;
            if ($evaluacion->user) {
                $nombreDocente = $evaluacion->user->name;
            }

            // Verificar si estamos en modo trial
            $trialMode = env('APP_TRIAL_MODE', true);
            $limitarRegistros = $trialMode;

            // Verificar si existe la plantilla usando separadores de ruta consistentes
            $templatePath = str_replace('/', DIRECTORY_SEPARATOR, storage_path('app/templates/evaluacion_template.xlsx'));
            $templateExists = file_exists($templatePath);
            
            \Log::info('Controller Export - Verificando plantilla en: ' . $templatePath);
            \Log::info('Controller Export - Plantilla existe: ' . ($templateExists ? 'SÍ' : 'NO'));

            if (!$templateExists) {
                \Log::warning('Controller Export - Plantilla no encontrada. Usando método sin plantilla.');
                
                // Crear una instancia de la clase Form para usar su método de exportación sin plantilla
                $formComponent = new \App\Livewire\Evaluacion\Form();
                
                try {
                    // Llamar al método exportarExcelSinPlantilla pasando los parámetros necesarios
                    $result = $formComponent->exportarExcelSinPlantilla($evaluacion, $nombreDocente, $limitarRegistros);
                    
                    \Log::info('Controller Export - Exportación sin plantilla ejecutada con éxito');
                    
                    if (!$result) {
                        \Log::error('Controller Export - El método sin plantilla devolvió NULL o false');
                        throw new \Exception('El método de exportación sin plantilla falló al generar el archivo');
                    }
                    
                    return $result;
                    
                } catch (\Exception $innerEx) {
                    \Log::error('Controller Export - Error en exportación sin plantilla: ' . $innerEx->getMessage());
                    \Log::error($innerEx->getTraceAsString());
                    
                    // Mostrar un mensaje de error más descriptivo
                    return redirect()->back()->with('error', 'Error al exportar sin plantilla: ' . $innerEx->getMessage());
                }
            }

            // Asegurar que el directorio temp existe
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                \Log::info('Creando directorio temporal: ' . $tempDir);
                mkdir($tempDir, 0755, true);
            }

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

            // Devolver la respuesta directamente
            return response()->download($tempFile, $downloadFilename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Error en exportación Excel desde controlador: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Exportar evaluación a PDF (separado de Livewire)
     */
    public function exportarPdf($id)
    {
        try {
            \Log::info('Iniciando exportación PDF en controlador - ID: ' . $id);
            
            // 1. Verificar que existe la sesión con los datos necesarios
            if (!session()->has('pdf_export')) {
                \Log::error('PDF Export - Sesión no encontrada');
                return redirect()->back()->with('error', 'Datos de exportación no encontrados. Intente nuevamente.');
            }
            
            // 2. Recuperar y validar datos de la sesión
            $data = session('pdf_export');
            
            if ($data['evaluacion_id'] != $id || now()->isAfter($data['expires_at'])) {
                \Log::error('PDF Export - Datos de sesión inválidos o expirados');
                session()->forget('pdf_export');
                return redirect()->back()->with('error', 'Los datos de exportación han expirado. Intente nuevamente.');
            }
            
            // 3. Cargar la evaluación
            $evaluacion = \App\Models\Evaluacion::with(['campoFormativo', 'detalles.alumno', 'detalles.criterios'])
                ->findOrFail($id);
            
            $nombreDocente = $data['docente'];
            \Log::info('PDF Export - Evaluación cargada, Docente: ' . $nombreDocente);
            
            // 4. Generar el PDF con manejo de errores mejorado
            \Log::info('PDF Export - Iniciando generación');
            
            // Capturar errores durante la generación del PDF
            $previousErrorReporting = error_reporting();
            error_reporting(0); // Deshabilitar completamente la salida de errores
            
            try {
                // Crear el objeto de exportación
                $export = new \App\Exports\EvaluacionPdfExport($evaluacion, $nombreDocente);
                
                // Verificar que todas las vistas existen
                $view = 'exports.evaluacion-pdf';
                if (!view()->exists($view)) {
                    throw new \Exception("Vista '$view' no encontrada. Compruebe que existe en resources/views/exports/");
                }
                
                // Exportar el PDF con manejo de excepciones
                $pdf = $export->export();
                
                // Verificar que el objeto PDF se generó correctamente
                if (!$pdf) {
                    throw new \Exception("El objeto PDF no se generó correctamente");
                }
                
                \Log::info('PDF Export - PDF generado correctamente');
            } catch (\Exception $pdfException) {
                \Log::error('Error específico al generar PDF: ' . $pdfException->getMessage());
                \Log::error($pdfException->getTraceAsString());
                throw $pdfException; // Re-lanzar para manejo externo
            } finally {
                // Restaurar configuración original
                error_reporting($previousErrorReporting);
            }
            
            // 5. Verificar que el PDF no esté vacío
            $output = $pdf->output();
            $outputSize = strlen($output);
            $outputFirst100Chars = substr($output, 0, 100);
            
            \Log::info('PDF Export - Tamaño del PDF: ' . $outputSize . ' bytes');
            \Log::info('PDF Export - Primeros 100 caracteres: ' . $outputFirst100Chars);
            
            // Verificar que comienza con %PDF (formato válido)
            if ($outputSize <= 0 || strpos($outputFirst100Chars, '%PDF') === false) {
                \Log::error('PDF Export - El contenido generado no parece ser un PDF válido');
                throw new \Exception("El PDF generado no es válido. Posible error en la plantilla o en los datos.");
            }
            
            // 6. Guardar PDF en archivo temporal
            $tempFilename = $data['temp_filename'];
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $tempFile = $tempDir . '/' . $tempFilename;
            $bytesWritten = file_put_contents($tempFile, $output);
            
            if (!$bytesWritten) {
                throw new \Exception("No se pudo escribir el archivo temporal");
            }
            
            \Log::info('PDF Export - Archivo guardado en: ' . $tempFile . ' (' . $bytesWritten . ' bytes)');
            
            // 7. Limpiar la sesión
            session()->forget('pdf_export');
            
            // 8. Enviar la respuesta de descarga con headers adecuados
            return response()->download($tempFile, 'evaluacion_' . $id . '.pdf', [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="evaluacion_' . $id . '.pdf"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Error en exportación PDF desde controlador: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            session()->forget('pdf_export'); // Limpiar sesión en caso de error
            return redirect()->back()->with('error', 'Error al exportar a PDF: ' . $e->getMessage());
        }
    }
}
