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

            // Verificar si existe la plantilla
            $templatePath = storage_path('app/templates/evaluacion_template.xlsx');

            if (!file_exists($templatePath)) {
                \Log::error('Plantilla no encontrada en: ' . $templatePath);
                return redirect()->back()->with('error', 'No se encontró la plantilla de Excel');
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
