<?php

namespace App\Exports;

use App\Models\Evaluacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionExport implements WithEvents, WithTitle
{
    protected $evaluacion;
    protected $detalles;
    protected $criterios;
    protected $templatePath;

    public function __construct(Evaluacion $evaluacion, $templatePath = null)
    {
        $this->evaluacion = $evaluacion;
        $this->templatePath = $templatePath ?: storage_path('app/templates/evaluacion_template.xlsx');

        // Cargar los datos necesarios
        $this->cargarDatos();
    }

    protected function cargarDatos()
    {
        $this->evaluacion->load(['campoFormativo', 'detalles.alumno', 'detalles.criterios']);
        $this->criterios = $this->evaluacion->campoFormativo->criterios()->orderBy('orden')->get();

        $this->detalles = [];
        foreach ($this->evaluacion->detalles as $detalle) {
            $calificaciones = [];
            $sumaPonderada = 0;
            $sumaPesos = 0;

            foreach ($this->criterios as $criterio) {
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

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Información básica de la evaluación
                $sheet->setCellValue('B3', $this->evaluacion->titulo);
                $sheet->setCellValue('B4', $this->evaluacion->campoFormativo->nombre);
                $sheet->setCellValue('B5', $this->evaluacion->fecha_evaluacion ? $this->evaluacion->fecha_evaluacion->format('d/m/Y') : 'No definida');
                $sheet->setCellValue('B6', $this->evaluacion->descripcion ?: 'Sin descripción');

                // Añadir los criterios de evaluación
                $startRow = 9;
                foreach ($this->criterios as $index => $criterio) {
                    $row = $startRow + $index;
                    $sheet->setCellValue('A' . $row, $criterio->nombre);
                    $sheet->setCellValue('B' . $row, $criterio->descripcion);
                    $sheet->setCellValue('C' . $row, $criterio->porcentaje . '%');
                }

                // Añadir los alumnos y sus calificaciones
                $startRow = 15;
                $startCol = 'A';

                // Encabezados de columnas para los criterios
                $colIndex = 1; // B
                foreach ($this->criterios as $criterio) {
                    $colLetter = chr(ord($startCol) + $colIndex);
                    $sheet->setCellValue($colLetter . $startRow, $criterio->nombre);
                    $colIndex++;
                }

                // Columna para promedio
                $promedioCol = chr(ord($startCol) + $colIndex);
                $sheet->setCellValue($promedioCol . $startRow, 'Promedio');

                // Datos de los alumnos
                foreach ($this->detalles as $index => $detalle) {
                    $row = $startRow + $index + 1;
                    $sheet->setCellValue($startCol . $row, $detalle['nombre']);

                    // Calificaciones por criterio
                    $colIndex = 1; // B
                    foreach ($detalle['calificaciones'] as $calificacion) {
                        $colLetter = chr(ord($startCol) + $colIndex);
                        $sheet->setCellValue($colLetter . $row, $calificacion['valor']);
                        $colIndex++;
                    }

                    // Promedio
                    $promedioCol = chr(ord($startCol) + $colIndex);
                    $sheet->setCellValue($promedioCol . $row, $detalle['promedio']);
                }

                // Ajustar estilos y formato (opcional)
                $lastRow = $startRow + count($this->detalles) + 1;
                $lastCol = $promedioCol;

                // Autoajustar columnas
                foreach(range($startCol, $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Añadir bordes y formatos (opcional)
                $range = $startCol . $startRow . ':' . $lastCol . $lastRow;
                $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Estilo para encabezados
                $headerRange = $startCol . $startRow . ':' . $lastCol . $startRow;
                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('DDDDDD');
            },
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Evaluación ' . $this->evaluacion->titulo;
    }

    /**
     * Crea un archivo de exportación usando una plantilla
     *
     * @return string Ruta del archivo temporal generado
     */
    public function exportFromTemplate()
    {
        if (!file_exists($this->templatePath)) {
            throw new \Exception("La plantilla no existe en: " . $this->templatePath);
        }

        // Cargar la plantilla
        $spreadsheet = IOFactory::load($this->templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Reemplazar los placeholders en la plantilla
        $sheet->setCellValue('B3', $this->evaluacion->titulo);
        $sheet->setCellValue('B4', $this->evaluacion->campoFormativo->nombre);
        $sheet->setCellValue('B5', $this->evaluacion->fecha_evaluacion ? $this->evaluacion->fecha_evaluacion->format('d/m/Y') : 'No definida');
        $sheet->setCellValue('B6', $this->evaluacion->descripcion ?: 'Sin descripción');

        // Ubicaciones de las tablas en la plantilla
        $criteriosStartRow = 9; // Fila donde inicia la tabla de criterios
        $alumnosStartRow = 15;  // Fila donde inicia la tabla de alumnos

        // Rellenar criterios (dinámicamente)
        foreach ($this->criterios as $index => $criterio) {
            $row = $criteriosStartRow + $index + 1; // +1 porque la fila 9 es el encabezado
            $sheet->setCellValue('A' . $row, $criterio->nombre);
            $sheet->setCellValue('B' . $row, $criterio->descripcion);
            $sheet->setCellValue('C' . $row, $criterio->porcentaje . '%');
        }

        // Limpiar filas de criterios no utilizadas (hasta 5 criterios por defecto en plantilla)
        for ($i = count($this->criterios) + 1; $i <= 5; $i++) {
            $row = $criteriosStartRow + $i;
            $sheet->setCellValue('A' . $row, '');
            $sheet->setCellValue('B' . $row, '');
            $sheet->setCellValue('C' . $row, '');
        }

        // Rellenar encabezados de calificaciones con los nombres de los criterios
        $startCol = 'B';
        $colIndex = 0;

        // Primero, limpiar los encabezados existentes
        for ($i = 0; $i < 5; $i++) { // Suponiendo un máximo de 5 criterios en la plantilla
            $colLetter = chr(ord($startCol) + $i);
            $sheet->setCellValue($colLetter . $alumnosStartRow, ''); // Limpiar los encabezados
        }

        // Luego, configurar los encabezados reales
        foreach ($this->criterios as $index => $criterio) {
            $colLetter = chr(ord($startCol) + $index);
            $sheet->setCellValue($colLetter . $alumnosStartRow, $criterio->nombre);
        }

        // Columna para promedio (justo después del último criterio)
        $promedioCol = chr(ord($startCol) + count($this->criterios));
        $sheet->setCellValue($promedioCol . $alumnosStartRow, 'Promedio');

        // Rellenar datos de alumnos (dinámicamente)
        foreach ($this->detalles as $index => $detalle) {
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

        // Limpiar filas de alumnos no utilizadas
        $totalFilasAlumnosPlantilla = 10; // Por defecto la plantilla tiene 10 filas para alumnos
        for ($i = count($this->detalles) + 1; $i <= $totalFilasAlumnosPlantilla; $i++) {
            $row = $alumnosStartRow + $i;
            $sheet->setCellValue('A' . $row, '');

            // Limpiar calificaciones y promedio
            for ($j = 0; $j <= 5; $j++) { // Suponiendo máximo 5 criterios + promedio
                $colLetter = chr(ord($startCol) + $j - 1);
                if ($j == 0) {
                    $colLetter = 'A'; // Primera columna (nombre alumno)
                }
                $sheet->setCellValue($colLetter . $row, '');
            }
        }

        // Si hay más alumnos que filas predefinidas, agregar filas según sea necesario
        if (count($this->detalles) > $totalFilasAlumnosPlantilla) {
            for ($i = $totalFilasAlumnosPlantilla + 1; $i <= count($this->detalles); $i++) {
                $row = $alumnosStartRow + $i;
                $detalle = $this->detalles[$i - 1];

                // Establecer nombre del alumno
                $sheet->setCellValue('A' . $row, $detalle['nombre']);

                // Establecer calificaciones
                foreach ($detalle['calificaciones'] as $calIndex => $calificacion) {
                    $colLetter = chr(ord($startCol) + $calIndex);
                    $sheet->setCellValue($colLetter . $row, $calificacion['valor']);
                }

                // Establecer promedio
                $sheet->setCellValue($promedioCol . $row, $detalle['promedio']);

                // Aplicar estilo a la nueva fila (bordes, etc.)
                $range = 'A' . $row . ':' . $promedioCol . $row;
                $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        }

        // Actualizar el pie de página con la fecha actual
        $footerRow = $alumnosStartRow + max(count($this->detalles) + 2, $totalFilasAlumnosPlantilla + 2);
        $sheet->setCellValue('A' . $footerRow, 'Esta evaluación fue generada el ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A' . $footerRow . ':' . $promedioCol . $footerRow);
        $sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $footerRow)->getFont()->setItalic(true);

        // Guardar el archivo en una ubicación temporal
        $tempFile = storage_path('app/temp/evaluacion_' . $this->evaluacion->id . '_' . time() . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }
}
