<?php

namespace App\Livewire\Evaluacion;

use App\Models\CampoFormativo;
use App\Models\Evaluacion;
use App\Exports\MultipleCamposFormativosExport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $campoFormativoFilter = '';
    public $evaluacionId;
    public $showDeleteModal = false;
    public $seleccionMultiple = false;
    public $evaluacionesSeleccionadas = [];
    public $showExportModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'campoFormativoFilter' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCampoFormativoFilter()
    {
        $this->resetPage();
    }

    public function toggleSeleccionMultiple()
    {
        $this->seleccionMultiple = !$this->seleccionMultiple;
        $this->evaluacionesSeleccionadas = [];
    }

    public function toggleSeleccionEvaluacion($id)
    {
        if (in_array($id, $this->evaluacionesSeleccionadas)) {
            $this->evaluacionesSeleccionadas = array_diff($this->evaluacionesSeleccionadas, [$id]);
        } else {
            $this->evaluacionesSeleccionadas[] = $id;
        }
    }

    public function showExportModal()
    {
        // Intentar escribir directamente en el log de Laravel
        file_put_contents(storage_path('logs/custom_log.txt'), "Método showExportModal llamado\n", FILE_APPEND);

        if (empty($this->evaluacionesSeleccionadas)) {
            file_put_contents(storage_path('logs/custom_log.txt'), "No hay evaluaciones seleccionadas\n", FILE_APPEND);
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Debe seleccionar al menos una evaluación para exportar'
            ]);
            return;
        }

        file_put_contents(storage_path('logs/custom_log.txt'), "Evaluaciones seleccionadas: " . implode(', ', $this->evaluacionesSeleccionadas) . "\n", FILE_APPEND);

        // Ejecutar directamente la exportación sin confirmación
        return $this->exportacionSimple();
    }

    public function exportacionSimple()
    {
        file_put_contents(storage_path('logs/custom_log.txt'), "Método exportacionSimple llamado\n", FILE_APPEND);

        if (empty($this->evaluacionesSeleccionadas)) {
            file_put_contents(storage_path('logs/custom_log.txt'), "Sin evaluaciones seleccionadas para exportar\n", FILE_APPEND);
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Debe seleccionar al menos una evaluación para exportar'
            ]);
            return;
        }

        try {
            file_put_contents(storage_path('logs/custom_log.txt'), "Iniciando proceso de exportación simple\n", FILE_APPEND);

            // Mostrar mensaje para indicar que se está procesando
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Procesando exportación simple...'
            ]);

            // Crear un archivo CSV simple
            $output = "ID,Título,Campo Formativo,Fecha\n";

            // Obtener las evaluaciones seleccionadas
            $evaluaciones = Evaluacion::with('campoFormativo')
                ->whereIn('id', $this->evaluacionesSeleccionadas)
                ->get();

            file_put_contents(storage_path('logs/custom_log.txt'), "Recuperadas " . count($evaluaciones) . " evaluaciones\n", FILE_APPEND);

            foreach ($evaluaciones as $evaluacion) {
                $output .= $evaluacion->id . ",";
                $output .= '"' . str_replace('"', '""', $evaluacion->titulo) . '",';
                $output .= '"' . str_replace('"', '""', $evaluacion->campoFormativo->nombre) . '",';
                $output .= ($evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'N/A') . "\n";
            }

            // Crear archivo temporal
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempFile = $tempDir . '/evaluaciones_simple_' . time() . '.csv';
            file_put_contents($tempFile, $output);

            file_put_contents(storage_path('logs/custom_log.txt'), "Archivo CSV generado: $tempFile\n", FILE_APPEND);

            if (!file_exists($tempFile)) {
                throw new \Exception('No se pudo crear el archivo CSV');
            }

            if (filesize($tempFile) === 0) {
                throw new \Exception('El archivo CSV está vacío');
            }

            // Mostrar mensaje de éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Exportación simple completada, iniciando descarga...'
            ]);

            return response()->download($tempFile, 'evaluaciones_' . date('Y-m-d_H-i-s') . '.csv', [
                'Content-Type' => 'text/csv',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            file_put_contents(storage_path('logs/custom_log.txt'), "Error en exportación simple: " . $e->getMessage() . "\n", FILE_APPEND);

            // Mostrar error
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error en exportación simple: ' . $e->getMessage()
            ]);
            return null;
        }
    }

    public function confirmDelete($id)
    {
        $this->evaluacionId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->reset(['evaluacionId', 'showDeleteModal']);
    }

    public function deleteEvaluacion()
    {
        $evaluacion = Evaluacion::findOrFail($this->evaluacionId);
        $evaluacion->delete();

        $this->reset(['evaluacionId', 'showDeleteModal']);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Evaluación eliminada correctamente']);
    }

    #[On('exportarSimple')]
    public function exportarSimple($data = null)
    {
        // Log para depuración
        file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Método exportarSimple llamado\n", FILE_APPEND);
        file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Data recibida: " . json_encode($data) . "\n", FILE_APPEND);

        try {
            // Verificar si estamos en modo trial - usando la misma lógica que en Show.php
            $appTrialMode = env('APP_TRIAL_MODE', true);
            $userIsTrial = auth()->check() && auth()->user()->trial;

            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - APP_TRIAL_MODE: " . ($appTrialMode ? 'SÍ' : 'NO') . "\n", FILE_APPEND);
            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Usuario tiene flag trial: " . ($userIsTrial ? 'SÍ' : 'NO') . "\n", FILE_APPEND);

            // Si la aplicación está en modo trial o el usuario tiene flag trial, bloquear
            if ($appTrialMode && $userIsTrial) {
                file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Bloqueando exportación para usuario trial\n", FILE_APPEND);
                // Mostrar mensaje de restricción con SweetAlert en lugar de notificación
                $this->dispatch('trial-feature-disabled', [
                    'title' => 'Función Premium',
                    'message' => 'La exportación a Excel es una característica premium. Para acceder a esta funcionalidad, adquiera una membresía completa.'
                ]);
                return null;
            }

            // Notificar a la UI que se recibió la llamada
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Procesando solicitud de exportación completa...'
            ]);

            // Si no hay datos o los datos están en un formato incorrecto, usar las evaluaciones ya seleccionadas
            if (empty($data) || !isset($data['ids']) || empty($data['ids'])) {
                file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - No se recibieron IDs válidos, usando evaluaciones ya seleccionadas: " . json_encode($this->evaluacionesSeleccionadas) . "\n", FILE_APPEND);

                // Si no hay evaluaciones seleccionadas, mostrar error
                if (empty($this->evaluacionesSeleccionadas)) {
                    $this->dispatch('notify', [
                        'type' => 'warning',
                        'message' => 'No hay evaluaciones seleccionadas para exportar'
                    ]);
                    return;
                }
            } else {
                // Si hay datos válidos, actualizar las evaluaciones seleccionadas
                $this->evaluacionesSeleccionadas = $data['ids'];
                file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Usando IDs recibidos: " . json_encode($this->evaluacionesSeleccionadas) . "\n", FILE_APPEND);
            }

            // Debido a problemas con el exportador, usaremos el método alternativo directamente
            return $this->exportarExcelAlternativo();

        } catch (\Exception $e) {
            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Error general: " . $e->getMessage() . "\n", FILE_APPEND);

            // Error general
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error general: ' . $e->getMessage()
            ]);
        }

        return null;
    }

    private function exportarExcelAlternativo()
    {
        try {
            // Incluir PhpSpreadsheet
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                throw new \Exception('La librería PhpSpreadsheet no está disponible');
            }

            // Verificar si estamos en modo trial - usando la misma lógica que en Show.php
            $appTrialMode = env('APP_TRIAL_MODE', true);
            $userIsTrial = auth()->check() && auth()->user()->trial;

            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - [Excel] APP_TRIAL_MODE: " . ($appTrialMode ? 'SÍ' : 'NO') . "\n", FILE_APPEND);
            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - [Excel] Usuario tiene flag trial: " . ($userIsTrial ? 'SÍ' : 'NO') . "\n", FILE_APPEND);

            // Si la aplicación está en modo trial o el usuario tiene flag trial, bloquear
            if ($appTrialMode && $userIsTrial) {
                file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - [Excel] Bloqueando exportación para usuario trial\n", FILE_APPEND);
                throw new \Exception('Funcionalidad premium no disponible en modo prueba');
            }

            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Generando Excel con PhpSpreadsheet\n", FILE_APPEND);

            // Crear nuevo objeto Spreadsheet para múltiples hojas
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Obtener evaluaciones con todas las relaciones necesarias
            $evaluaciones = Evaluacion::with([
                'campoFormativo',
                'detalles.alumno',
                'detalles.criterios', // Importante: Carga los criterios relacionados
                'campoFormativo.criterios' // Cargar criterios del campo formativo
            ])
            ->whereIn('id', $this->evaluacionesSeleccionadas)
            ->get();

            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Recuperadas " . count($evaluaciones) . " evaluaciones con detalles para Excel\n", FILE_APPEND);

            // Datos generales para mostrar en el encabezado
            $nombreMaestro = "Maestro: " . auth()->user()->name ?? 'Sistema de Calificaciones';
            $grupoInfo = "Grupo: " . ($evaluaciones->first()->detalles->first()->alumno->grupo->nombre ?? 'General');
            $fechaGeneracion = "Fecha de generación: " . now()->format('d/m/Y H:i');

            // CREACIÓN DE LA HOJA PRINCIPAL CON TODAS LAS EVALUACIONES
            $mainSheet = $spreadsheet->getActiveSheet();
            $mainSheet->setTitle('Todas las Evaluaciones');

            // Agregar la fila de cabecera con los datos generales en la hoja principal
            $mainSheet->setCellValue('A1', $nombreMaestro);
            $mainSheet->setCellValue('A2', $grupoInfo);
            $mainSheet->setCellValue('A3', $fechaGeneracion);

            // Utilizado para calcular el ancho de la tabla en la hoja principal
            $maxNumberOfCriterios = 0;
            $row = 6; // Fila inicial para datos en la hoja principal (dejando espacio para el encabezado)

            // Para cada evaluación
            foreach ($evaluaciones as $evaluacionIndex => $evaluacion) {
                $campoFormativo = $evaluacion->campoFormativo;
                $detalles = $evaluacion->detalles;

                // Obtener el MOMENTO del campo formativo
                $momento = "PRIMER MOMENTO";
                if ($campoFormativo && isset($campoFormativo->momento)) {
                    $momento = $campoFormativo->momento;
                }

                file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Procesando evaluación ID: " . $evaluacion->id . " Campo Formativo: " . optional($campoFormativo)->nombre . " Momento: " . $momento . "\n", FILE_APPEND);

                // Obtener solo los criterios específicos de esta evaluación (a través de su campo formativo)
                $criterios = [];
                if ($campoFormativo && isset($campoFormativo->criterios)) {
                    foreach ($campoFormativo->criterios as $criterio) {
                        $criterios[] = [
                            'id' => $criterio->id,
                            'nombre' => $criterio->nombre,
                            'porcentaje' => $criterio->porcentaje ?? 0,
                            'orden' => $criterio->orden ?? $criterio->id
                        ];
                    }

                    // Ordenar criterios por orden
                    usort($criterios, function($a, $b) {
                        return $a['orden'] <=> $b['orden'];
                    });

                    // Actualizar el número máximo de criterios para calcular el ancho de tabla
                    $maxNumberOfCriterios = max($maxNumberOfCriterios, count($criterios));
                }

                // Calcular el rango de celdas para esta evaluación
                $columnaInicial = 'I'; // Columna donde empiezan los criterios
                $columnaPromedio = chr(ord($columnaInicial) + count($criterios));
                $lastColumn = $columnaPromedio;

                // Agregar título de la evaluación en la hoja principal
                $mainSheet->setCellValue('A' . $row, 'EVALUACIÓN: ' . $evaluacion->titulo . ' - ' . optional($campoFormativo)->nombre . ' - ' . $momento);
                $mainSheet->mergeCells('A' . $row . ':' . $lastColumn . $row);
                $mainSheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $mainSheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFill()->getStartColor()->setARGB('FFCCFFCC'); // Verde claro
                $mainSheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFont()->setBold(true);

                $row++; // Avanzar a la siguiente fila

                // Establecer encabezados para esta evaluación en la hoja principal
                $mainSheet->setCellValue('A' . $row, 'Título Evaluación');
                $mainSheet->setCellValue('B' . $row, 'Campo Formativo');
                $mainSheet->setCellValue('C' . $row, 'Fecha');
                $mainSheet->setCellValue('D' . $row, 'MOMENTO');
                $mainSheet->setCellValue('E' . $row, 'Nombre Alumno');
                $mainSheet->setCellValue('F' . $row, 'Apellido Paterno');
                $mainSheet->setCellValue('G' . $row, 'Apellido Materno');

                // Agregar encabezados de criterios específicos de esta evaluación en la hoja principal
                foreach ($criterios as $index => $criterio) {
                    $columna = chr(ord('H') + $index);
                    $porcentaje = isset($criterio['porcentaje']) && $criterio['porcentaje'] > 0 ? ' (' . $criterio['porcentaje'] . '%)' : '';
                    $mainSheet->setCellValue($columna . $row, $criterio['nombre'] . $porcentaje);
                }

                // Columna para promedio (después del último criterio)
                $mainSheet->setCellValue($columnaPromedio . $row, 'PROMEDIO');

                // Dar formato al encabezado en la hoja principal
                $mainSheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $mainSheet->getStyle('A' . $row . ':' . $lastColumn . $row)->getFill()->getStartColor()->setARGB('FFFFFF00'); // Amarillo

                $row++; // Avanzar a la siguiente fila para los datos

                // Agregar datos de alumnos para esta evaluación en la hoja principal
                if ($detalles->count() > 0) {
                    foreach ($detalles as $detalle) {
                        $alumno = $detalle->alumno;

                        // Datos de la evaluación en la hoja principal
                        $mainSheet->setCellValue('A' . $row, $evaluacion->titulo);
                        $mainSheet->setCellValue('B' . $row, optional($campoFormativo)->nombre ?? 'N/A');
                        $mainSheet->setCellValue('C' . $row, $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'N/A');
                        $mainSheet->setCellValue('D' . $row, $momento);

                        // Datos del alumno en la hoja principal
                        $mainSheet->setCellValue('E' . $row, $alumno ? $alumno->nombre : 'N/A');
                        $mainSheet->setCellValue('F' . $row, $alumno ? $alumno->apellido_paterno : 'N/A');
                        $mainSheet->setCellValue('G' . $row, $alumno ? $alumno->apellido_materno : 'N/A');

                        // Criterios específicos de esta evaluación en la hoja principal
                        $sumaCriterios = 0;
                        $countCriterios = 0;

                        foreach ($criterios as $index => $criterioInfo) {
                            $columna = chr(ord('H') + $index);
                            $valor = '';

                            // Buscar calificación para este criterio
                            if ($detalle->criterios) {
                                foreach ($detalle->criterios as $criterio) {
                                    if ($criterio->id == $criterioInfo['id']) {
                                        // Verificar si existe un campo calificacion en la relación pivot
                                        if (isset($criterio->pivot) && isset($criterio->pivot->calificacion)) {
                                            $valor = $criterio->pivot->calificacion;
                                        } else if (isset($criterio->calificacion)) {
                                            $valor = $criterio->calificacion;
                                        }

                                        if (is_numeric($valor)) {
                                            $sumaCriterios += $valor;
                                            $countCriterios++;
                                        }
                                        break;
                                    }
                                }
                            }

                            $mainSheet->setCellValue($columna . $row, $valor);
                        }

                        // Calcular promedio en la hoja principal
                        $promedio = $countCriterios > 0 ? $sumaCriterios / $countCriterios : '';
                        $mainSheet->setCellValue($columnaPromedio . $row, $promedio);

                        $row++;
                    }
                } else {
                    // Si la evaluación no tiene detalles, agregar una fila informativa en la hoja principal
                    $mainSheet->setCellValue('A' . $row, $evaluacion->titulo);
                    $mainSheet->setCellValue('B' . $row, optional($campoFormativo)->nombre ?? 'N/A');
                    $mainSheet->setCellValue('C' . $row, $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'N/A');
                    $mainSheet->setCellValue('D' . $row, $momento);
                    $mainSheet->setCellValue('E' . $row, 'No hay alumnos evaluados');
                    $mainSheet->mergeCells('E' . $row . ':' . $lastColumn . $row);

                    $row++;
                }

                // Agregar dos filas vacías después de cada evaluación en la hoja principal para separarlas (excepto la última)
                if ($evaluacionIndex < count($evaluaciones) - 1) {
                    $row += 2;
                }

                // CREAR HOJA INDIVIDUAL PARA ESTA EVALUACIÓN
                // Crear una nueva hoja para cada evaluación
                $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Eval ' . ($evaluacionIndex + 1) . ' - ' . substr($evaluacion->titulo, 0, 25));
                $spreadsheet->addSheet($sheet);

                // Agregar la fila de cabecera con los datos generales en la hoja individual
                $sheet->setCellValue('A1', $nombreMaestro);
                $sheet->setCellValue('A2', $grupoInfo);
                $sheet->setCellValue('A3', $fechaGeneracion);

                // Calculamos el rango de celdas a combinar (hasta la última columna que vamos a usar)
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $sheet->mergeCells('A3:' . $lastColumn . '3');

                // Aplicar estilos al encabezado
                $sheet->getStyle('A1:' . $lastColumn . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle('A1:' . $lastColumn . '3')->getFill()->getStartColor()->setARGB('FFCCFFCC'); // Verde claro
                $sheet->getStyle('A1:' . $lastColumn . '3')->getFont()->setBold(true);

                // Información de la evaluación en la hoja individual (ahora comienza en fila 4)
                $sheet->setCellValue('A4', 'Evaluación:');
                $sheet->setCellValue('B4', $evaluacion->titulo);
                $sheet->setCellValue('A5', 'Campo Formativo:');
                $sheet->setCellValue('B5', optional($campoFormativo)->nombre ?? 'N/A');
                $sheet->setCellValue('D5', 'Momento:');
                $sheet->setCellValue('E5', $momento);

                // Establecer encabezados en la hoja individual (ahora comienza en fila 6)
                $sheet->setCellValue('A6', 'Título Evaluación');
                $sheet->setCellValue('B6', 'Campo Formativo');
                $sheet->setCellValue('C6', 'Fecha');
                $sheet->setCellValue('D6', 'MOMENTO');
                $sheet->setCellValue('E6', 'Nombre Alumno');
                $sheet->setCellValue('F6', 'Apellido Paterno');
                $sheet->setCellValue('G6', 'Apellido Materno');

                // Agregar encabezados de criterios específicos de esta evaluación en la hoja individual
                foreach ($criterios as $index => $criterio) {
                    $columna = chr(ord('H') + $index);
                    $porcentaje = isset($criterio['porcentaje']) && $criterio['porcentaje'] > 0 ? ' (' . $criterio['porcentaje'] . '%)' : '';
                    $sheet->setCellValue($columna . '6', $criterio['nombre'] . $porcentaje);
                }

                // Columna para promedio (después del último criterio) en la hoja individual
                $sheet->setCellValue($columnaPromedio . '6', 'PROMEDIO');

                // Dar formato al encabezado en la hoja individual
                $sheet->getStyle('A6:' . $lastColumn . '6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle('A6:' . $lastColumn . '6')->getFill()->getStartColor()->setARGB('FFFFFF00'); // Amarillo

                // Iniciar desde la fila 7 para los datos en la hoja individual
                $sheetRow = 7;

                if ($detalles->count() > 0) {
                    foreach ($detalles as $detalle) {
                        $alumno = $detalle->alumno;

                        // Datos de la evaluación en la hoja individual
                        $sheet->setCellValue('A' . $sheetRow, $evaluacion->titulo);
                        $sheet->setCellValue('B' . $sheetRow, optional($campoFormativo)->nombre ?? 'N/A');
                        $sheet->setCellValue('C' . $sheetRow, $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'N/A');
                        $sheet->setCellValue('D' . $sheetRow, $momento);

                        // Datos del alumno en la hoja individual
                        $sheet->setCellValue('E' . $sheetRow, $alumno ? $alumno->nombre : 'N/A');
                        $sheet->setCellValue('F' . $sheetRow, $alumno ? $alumno->apellido_paterno : 'N/A');
                        $sheet->setCellValue('G' . $sheetRow, $alumno ? $alumno->apellido_materno : 'N/A');

                        // Criterios específicos de esta evaluación en la hoja individual
                        $sumaCriterios = 0;
                        $countCriterios = 0;

                        foreach ($criterios as $index => $criterioInfo) {
                            $columna = chr(ord('H') + $index);
                            $valor = '';

                            // Buscar calificación para este criterio
                            if ($detalle->criterios) {
                                foreach ($detalle->criterios as $criterio) {
                                    if ($criterio->id == $criterioInfo['id']) {
                                        // Verificar si existe un campo calificacion en la relación pivot
                                        if (isset($criterio->pivot) && isset($criterio->pivot->calificacion)) {
                                            $valor = $criterio->pivot->calificacion;
                                        } else if (isset($criterio->calificacion)) {
                                            $valor = $criterio->calificacion;
                                        }

                                        if (is_numeric($valor)) {
                                            $sumaCriterios += $valor;
                                            $countCriterios++;
                                        }
                                        break;
                                    }
                                }
                            }

                            $sheet->setCellValue($columna . $sheetRow, $valor);
                        }

                        // Calcular promedio en la hoja individual
                        $promedio = $countCriterios > 0 ? $sumaCriterios / $countCriterios : '';
                        $sheet->setCellValue($columnaPromedio . $sheetRow, $promedio);

                        $sheetRow++;
                    }
                } else {
                    // Si la evaluación no tiene detalles, agregar una fila informativa en la hoja individual
                    $sheet->setCellValue('A' . $sheetRow, $evaluacion->titulo);
                    $sheet->setCellValue('B' . $sheetRow, optional($campoFormativo)->nombre ?? 'N/A');
                    $sheet->setCellValue('C' . $sheetRow, $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'N/A');
                    $sheet->setCellValue('D' . $sheetRow, $momento);
                    $sheet->setCellValue('E' . $sheetRow, 'No hay alumnos evaluados');
                    $sheet->mergeCells('E' . $sheetRow . ':' . $columnaPromedio . $sheetRow);

                    $sheetRow++;
                }

                // Auto-ajustar anchos de columna en la hoja individual
                foreach(range('A', $columnaPromedio) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }

            // Ajustar la celda de título en la hoja principal usando el número máximo de criterios
            $maxLastColumn = chr(ord('H') + $maxNumberOfCriterios + 1); // H + número máximo de criterios + 1 para promedio

            // Aplicar ajuste a las filas de encabezado en la hoja principal
            $mainSheet->mergeCells('A1:' . $maxLastColumn . '1');
            $mainSheet->mergeCells('A2:' . $maxLastColumn . '2');
            $mainSheet->mergeCells('A3:' . $maxLastColumn . '3');

            // Aplicar estilos al encabezado de la hoja principal
            $mainSheet->getStyle('A1:' . $maxLastColumn . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $mainSheet->getStyle('A1:' . $maxLastColumn . '3')->getFill()->getStartColor()->setARGB('FFCCFFCC'); // Verde claro
            $mainSheet->getStyle('A1:' . $maxLastColumn . '3')->getFont()->setBold(true);

            // Auto-ajustar anchos de columna en la hoja principal
            foreach(range('A', $maxLastColumn) as $col) {
                $mainSheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Si no hay hojas, mostrar un mensaje de error
            if ($spreadsheet->getSheetCount() == 0) {
                throw new \Exception('No se pudieron generar hojas para las evaluaciones');
            }

            // Activar la primera hoja (hoja principal)
            $spreadsheet->setActiveSheetIndex(0);

            // Crear el archivo Excel
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            // Guardar el archivo
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $filename = 'evaluaciones_completas_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = $tempDir . '/' . $filename;

            $writer->save($tempFile);

            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Archivo Excel generado: $tempFile\n", FILE_APPEND);

            if (!file_exists($tempFile)) {
                throw new \Exception('No se pudo crear el archivo Excel');
            }

            // Mostrar mensaje de éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Exportación a Excel completada, iniciando descarga...'
            ]);

            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - Error en exportación alternativa: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine() . "\n", FILE_APPEND);
            file_put_contents(storage_path('logs/custom_log.txt'), date('Y-m-d H:i:s') . " - " . $e->getTraceAsString() . "\n", FILE_APPEND);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error en exportación a Excel: ' . $e->getMessage()
            ]);

            return null;
        }
    }

    public function render()
    {
        $query = Evaluacion::query()
            ->with(['detalles.alumno', 'campoFormativo'])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('titulo', 'like', '%' . $this->search . '%')
                      ->orWhereHas('detalles.alumno', function ($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->campoFormativoFilter, function ($query) {
                $query->where('campo_formativo_id', $this->campoFormativoFilter);
            });

        return view('livewire.evaluacion.index', [
            'evaluaciones' => $query->latest()->paginate(10),
            'camposFormativos' => CampoFormativo::all()
        ]);
    }
}
