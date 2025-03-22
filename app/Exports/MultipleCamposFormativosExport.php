<?php

namespace App\Exports;

use App\Models\Evaluacion;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;

class MultipleCamposFormativosExport implements WithEvents, WithMultipleSheets
{
    protected $evaluaciones;
    protected $templatePath;
    protected $nombreDocente;

    /**
     * @param array $evaluacionesIds IDs de las evaluaciones a exportar
     * @param string $nombreDocente Nombre del docente que realiza la exportación
     */
    public function __construct(array $evaluacionesIds, $nombreDocente)
    {
        $this->evaluaciones = Evaluacion::with(['campoFormativo', 'detalles.alumno', 'detalles.criterios'])
            ->whereIn('id', $evaluacionesIds)
            ->get();

        $this->templatePath = storage_path('app/templates/evaluacion_template.xlsx');
        $this->nombreDocente = $nombreDocente;

        Log::info('MultipleCamposFormativosExport creado con ' . count($this->evaluaciones) . ' evaluaciones');
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->evaluaciones as $evaluacion) {
            Log::info('Añadiendo hoja para evaluación: ' . $evaluacion->id . ' - ' . $evaluacion->titulo);
            $sheets[] = new EvaluacionExport($evaluacion, $this->templatePath, $this->nombreDocente, false);
        }

        return $sheets;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function(BeforeExport $event) {
                $event->writer->setCreator('Sistema de Calificaciones');
                $event->writer->setTitle('Exportación de Múltiples Campos Formativos');

                Log::info('Evento BeforeExport en MultipleCamposFormativosExport');
            },
        ];
    }

    /**
     * Exporta las evaluaciones seleccionadas en un solo archivo Excel
     *
     * @return string Ruta del archivo temporal generado
     */
    public function export()
    {
        Log::info('Iniciando exportación de múltiples campos formativos');

        // Verificar si existen las evaluaciones
        if ($this->evaluaciones->isEmpty()) {
            throw new \Exception('No se encontraron evaluaciones para exportar');
        }

        // Verificar si existe la plantilla
        if (!file_exists($this->templatePath)) {
            throw new \Exception("La plantilla no existe en: " . $this->templatePath);
        }

        // Asegurar que el directorio temp existe
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            // Generar un nombre de archivo temporal
            $tempFile = storage_path('app/temp/multiple_evaluaciones_' . time() . '.xlsx');

            // Crear un nuevo libro Excel con varias hojas (una por evaluación)
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            // Eliminar la hoja predeterminada
            $spreadsheet->removeSheetByIndex(0);

            // Añadir una hoja por cada evaluación
            foreach ($this->evaluaciones as $index => $evaluacion) {
                Log::info('Procesando evaluación: ' . $evaluacion->id);

                // Cargar la plantilla para esta evaluación
                $templateSpreadsheet = IOFactory::load($this->templatePath);
                $sheet = $templateSpreadsheet->getActiveSheet();

                // Hacer una copia de la hoja de la plantilla
                $newSheet = clone $sheet;
                $newSheet->setTitle(substr('Eval: ' . $evaluacion->titulo, 0, 31)); // Título limitado a 31 caracteres

                // Añadir la hoja al libro principal
                $spreadsheet->addSheet($newSheet);

                // Obtener la hoja recién añadida para modificarla
                $activeSheet = $spreadsheet->getSheetByName($newSheet->getTitle());

                // Llenar los datos de la evaluación
                $this->fillEvaluacionData($activeSheet, $evaluacion);
            }

            // Guardar el archivo
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            Log::info('Archivo Excel con múltiples evaluaciones creado en: ' . $tempFile);

            return $tempFile;

        } catch (\Exception $e) {
            Log::error('Error al exportar múltiples evaluaciones: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Llena una hoja de Excel con los datos de una evaluación
     */
    protected function fillEvaluacionData($sheet, $evaluacion)
    {
        // Información básica de la evaluación
        $sheet->setCellValue('B3', $evaluacion->titulo);
        $sheet->setCellValue('B4', $evaluacion->campoFormativo->nombre);
        $sheet->setCellValue('B5', $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'No definida');
        $sheet->setCellValue('B6', $evaluacion->momento ? $evaluacion->momento->value : 'No definido');
        $sheet->setCellValue('B7', $evaluacion->descripcion ?: 'Sin descripción');
        $sheet->setCellValue('B8', $this->nombreDocente);

        // Dar formato a las celdas para asegurar que sean visibles
        $sheet->getStyle('A3:B8')->getFont()->setSize(11);
        $sheet->getStyle('A3:A8')->getFont()->setBold(true);

        // Ajustar el ancho de la columna B para dar espacio al nombre del docente
        $sheet->getColumnDimension('B')->setAutoSize(true);

        // Procesar criterios
        $criterios = $evaluacion->campoFormativo->criterios()->orderBy('orden')->get();
        $criteriosStartRow = 10;

        // Añadir el título de la sección de criterios
        $sheet->setCellValue('A' . ($criteriosStartRow - 1), 'CRITERIOS DE EVALUACIÓN');
        $sheet->getStyle('A' . ($criteriosStartRow - 1))->getFont()->setBold(true);

        // Rellenar criterios
        foreach ($criterios as $index => $criterio) {
            $row = $criteriosStartRow + $index;
            $sheet->setCellValue('A' . $row, $criterio->nombre);
            $sheet->setCellValue('B' . $row, $criterio->descripcion);
            $sheet->setCellValue('C' . $row, $criterio->porcentaje . '%');
        }

        // Calcular la fila de inicio para alumnos
        $alumnosStartRow = $criteriosStartRow + max($criterios->count(), 5) + 2;

        // Añadir el título de la sección de alumnos
        $sheet->setCellValue('A' . ($alumnosStartRow - 1), 'ALUMNOS EVALUADOS');
        $sheet->getStyle('A' . ($alumnosStartRow - 1))->getFont()->setBold(true);

        // Preparar datos de los alumnos y sus calificaciones
        $detalles = [];
        foreach ($evaluacion->detalles as $detalle) {
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

        // Encabezados de calificaciones
        $startCol = 'B';
        $colIndex = 0;

        foreach ($criterios as $index => $criterio) {
            $colLetter = chr(ord($startCol) + $index);
            $sheet->setCellValue($colLetter . $alumnosStartRow, $criterio->nombre);
        }

        // Columna para promedio
        $promedioCol = chr(ord($startCol) + $criterios->count());
        $sheet->setCellValue($promedioCol . $alumnosStartRow, 'Promedio');

        // Rellenar datos de alumnos
        foreach ($detalles as $index => $detalle) {
            $row = $alumnosStartRow + $index + 1;
            $sheet->setCellValue('A' . $row, $detalle['nombre']);

            // Calificaciones
            foreach ($detalle['calificaciones'] as $calIndex => $calificacion) {
                $colLetter = chr(ord($startCol) + $calIndex);
                $sheet->setCellValue($colLetter . $row, $calificacion['valor']);
            }

            // Promedio
            $sheet->setCellValue($promedioCol . $row, $detalle['promedio']);
        }

        // Dar formato a la tabla
        $lastRow = $alumnosStartRow + count($detalles);
        $lastCol = $promedioCol;

        // Aplicar bordes y alineación a todas las celdas de la tabla
        $tableRange = 'A' . $alumnosStartRow . ':' . $lastCol . $lastRow;
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Formato especial para la cabecera
        $headerRange = 'A' . $alumnosStartRow . ':' . $lastCol . $alumnosStartRow;
        $sheet->getStyle($headerRange)->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'FFE0E0E0'],
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        // Formato para la columna de nombres de alumnos
        $sheet->getStyle('A' . ($alumnosStartRow + 1) . ':A' . $lastRow)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Formato para la columna de promedios
        $sheet->getStyle($promedioCol . ($alumnosStartRow + 1) . ':' . $promedioCol . $lastRow)
            ->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFDFEFFF'],
                ],
                'font' => [
                    'bold' => true,
                ]
            ]);

        // Ajustar anchos de columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        for ($col = ord($startCol); $col <= ord($lastCol); $col++) {
            $sheet->getColumnDimension(chr($col))->setWidth(12);
        }

        // Añadir una sección de observaciones si existen
        $observacionesRow = $lastRow + 2;
        $sheet->setCellValue('A' . $observacionesRow, 'OBSERVACIONES GENERALES');
        $sheet->getStyle('A' . $observacionesRow)->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($observacionesRow + 1), $evaluacion->descripcion ?: 'Sin observaciones adicionales');
        $sheet->mergeCells('A' . ($observacionesRow + 1) . ':' . $lastCol . ($observacionesRow + 1));

        // Añadir pie de página con fechas
        $footerRow = $observacionesRow + 3;
        $sheet->setCellValue('A' . $footerRow, 'Fecha de creación: ' . $evaluacion->created_at->format('d/m/Y H:i'));
        $sheet->setCellValue('C' . $footerRow, 'Fecha de exportación: ' . now()->format('d/m/Y H:i'));

        // Ocultar las líneas de cuadrícula
        $sheet->setShowGridlines(false);

        Log::info('Hoja completada para evaluación ID: ' . $evaluacion->id);
    }
}
