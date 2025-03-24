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
}
