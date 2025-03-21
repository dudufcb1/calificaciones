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
    protected $nombreDocente;

    public function __construct(Evaluacion $evaluacion, $templatePath = null, $nombreDocente = null)
    {
        $this->evaluacion = $evaluacion;
        $this->templatePath = $templatePath ?: storage_path('app/templates/evaluacion_template.xlsx');
        $this->nombreDocente = $nombreDocente;

        // Cargar los datos necesarios
        $this->cargarDatos();
    }

    protected function cargarDatos()
    {
        // Cargar explícitamente la relación user para asegurar que tenemos los datos del docente
        $this->evaluacion->load(['campoFormativo', 'detalles.alumno', 'detalles.criterios', 'user']);
        $this->criterios = $this->evaluacion->campoFormativo->criterios()->orderBy('orden')->get();

        // Debugging: asegurar que el usuario está siendo cargado correctamente
        $nombreUsuario = $this->evaluacion->user ? $this->evaluacion->user->name : 'No encontrado';
        \Log::info('Usuario cargado para evaluación ' . $this->evaluacion->id . ': ' . $nombreUsuario);

        // Si no tenemos usuario en la relación pero tenemos un nombre de docente pasado por parámetro, usarlo
        if (!$this->nombreDocente) {
            if ($this->evaluacion->user) {
                $this->nombreDocente = $this->evaluacion->user->name;
            } elseif (auth()->check()) {
                $this->nombreDocente = auth()->user()->name;
            } else {
                $this->nombreDocente = 'No asignado';
            }
        }

        \Log::info('Nombre de docente que se usará: ' . $this->nombreDocente);

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
                $sheet->setCellValue('B6', $this->evaluacion->momento ? $this->evaluacion->momento->value : 'No definido');
                $sheet->setCellValue('B7', $this->evaluacion->descripcion ?: 'Sin descripción');

                // Usar directamente el nombre del docente que se guardó en el constructor
                \Log::info('Asignando nombre de docente a celda B8: ' . $this->nombreDocente);
                $sheet->setCellValue('B8', $this->nombreDocente);

                // Aplicar estilo específico para esta celda para asegurar visibilidad
                $sheet->getStyle('B8')->getFont()->setBold(false)->setSize(11);
                $sheet->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                // Dar formato a las celdas de información básica para asegurar que sean visibles
                $sheet->getStyle('A3:B8')->getFont()->setSize(11);
                $sheet->getStyle('A3:A8')->getFont()->setBold(true);

                // Actualizar los encabezados de las celdas
                $sheet->setCellValue('A3', 'Título:');
                $sheet->setCellValue('A4', 'Campo Formativo:');
                $sheet->setCellValue('A5', 'Fecha:');
                $sheet->setCellValue('A6', 'Momento:');
                $sheet->setCellValue('A7', 'Descripción:');
                $sheet->setCellValue('A8', 'Docente:');

                // Ajustar el ancho de la columna B para dar espacio al nombre del docente
                $sheet->getColumnDimension('B')->setAutoSize(true);

                // Definir el inicio de la sección de criterios
                $startRow = 10; // Ajustamos la fila de inicio para criterios

                // Añadir el título de la sección de criterios
                $sheet->setCellValue('A' . ($startRow - 1), 'CRITERIOS DE EVALUACIÓN');
                $sheet->getStyle('A' . ($startRow - 1))->getFont()->setBold(true);

                // Añadir los criterios de evaluación
                foreach ($this->criterios as $index => $criterio) {
                    $row = $startRow + $index;
                    $sheet->setCellValue('A' . $row, $criterio->nombre);
                    $sheet->setCellValue('B' . $row, $criterio->descripcion);
                    $sheet->setCellValue('C' . $row, $criterio->porcentaje . '%');
                }

                // Añadir los alumnos y sus calificaciones
                $alumnosStartRow = $startRow + count($this->criterios) + 2; // 2 filas adicionales para separación

                // Añadir el título de la sección de alumnos
                $sheet->setCellValue('A' . ($alumnosStartRow - 1), 'ALUMNOS EVALUADOS');
                $sheet->getStyle('A' . ($alumnosStartRow - 1))->getFont()->setBold(true);

                $startCol = 'A';

                // Encabezados de columnas para los criterios
                $colIndex = 1; // B
                foreach ($this->criterios as $criterio) {
                    $colLetter = chr(ord($startCol) + $colIndex);
                    $sheet->setCellValue($colLetter . $alumnosStartRow, $criterio->nombre);
                    $colIndex++;
                }

                // Columna para promedio
                $promedioCol = chr(ord($startCol) + $colIndex);
                $sheet->setCellValue($promedioCol . $alumnosStartRow, 'Promedio');

                // Datos de los alumnos
                foreach ($this->detalles as $index => $detalle) {
                    $row = $alumnosStartRow + $index + 1;
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
                $lastRow = $alumnosStartRow + count($this->detalles) + 1;
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
        $sheet->setCellValue('B6', $this->evaluacion->momento ? $this->evaluacion->momento->value : 'No definido');
        $sheet->setCellValue('B7', $this->evaluacion->descripcion ?: 'Sin descripción');

        // Usar directamente el nombre del docente que se guardó en el constructor
        \Log::info('Asignando nombre de docente a celda B8 (template): ' . $this->nombreDocente);
        $sheet->setCellValue('B8', $this->nombreDocente);

        // Aplicar estilo específico para esta celda para asegurar visibilidad
        $sheet->getStyle('B8')->getFont()->setBold(false)->setSize(11);
        $sheet->getStyle('B8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Dar formato a las celdas para asegurar que sean visibles
        $sheet->getStyle('A3:B8')->getFont()->setSize(11);
        $sheet->getStyle('A3:A8')->getFont()->setBold(true);

        // Ajustar el ancho de la columna B para dar espacio al nombre del docente
        $sheet->getColumnDimension('B')->setAutoSize(true);

        // Actualizar también los encabezados de las celdas
        $sheet->setCellValue('A3', 'Título:');
        $sheet->setCellValue('A4', 'Campo Formativo:');
        $sheet->setCellValue('A5', 'Fecha:');
        $sheet->setCellValue('A6', 'Momento:');
        $sheet->setCellValue('A7', 'Descripción:');
        $sheet->setCellValue('A8', 'Docente:');

        // Ubicaciones de las tablas en la plantilla
        $criteriosStartRow = 10; // Ajustamos la fila de inicio para criterios

        // Añadir el título de la sección de criterios
        $sheet->setCellValue('A' . ($criteriosStartRow - 1), 'CRITERIOS DE EVALUACIÓN');
        $sheet->getStyle('A' . ($criteriosStartRow - 1))->getFont()->setBold(true);

        // Rellenar criterios (dinámicamente)
        foreach ($this->criterios as $index => $criterio) {
            $row = $criteriosStartRow + $index; // No necesitamos +1 aquí porque ya tenemos el inicio correcto
            $sheet->setCellValue('A' . $row, $criterio->nombre);
            $sheet->setCellValue('B' . $row, $criterio->descripcion);
            $sheet->setCellValue('C' . $row, $criterio->porcentaje . '%');
        }

        // Limpiar filas de criterios no utilizadas (hasta 5 criterios por defecto en plantilla)
        for ($i = count($this->criterios); $i < 5; $i++) {
            $row = $criteriosStartRow + $i;
            $sheet->setCellValue('A' . $row, '');
            $sheet->setCellValue('B' . $row, '');
            $sheet->setCellValue('C' . $row, '');
        }

        // Calcular la fila de inicio para alumnos basada en la cantidad de criterios
        $alumnosStartRow = $criteriosStartRow + max(count($this->criterios), 5) + 2; // 2 filas de espacio

        // Añadir el título de la sección de alumnos
        $sheet->setCellValue('A' . ($alumnosStartRow - 1), 'ALUMNOS EVALUADOS');
        $sheet->getStyle('A' . ($alumnosStartRow - 1))->getFont()->setBold(true);

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
            for ($j = 0; $j <= count($this->criterios); $j++) { // Criterios + promedio
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

                // Promedio
                $sheet->setCellValue($promedioCol . $row, $detalle['promedio']);
            }
        }

        // Guardar el archivo en un directorio temporal
        $tempFile = storage_path('app/temp/evaluacion_' . $this->evaluacion->id . '_' . time() . '.xlsx');

        // Asegurarse de que el directorio temp existe
        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        // Crear el escritor y guardar el archivo
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        // Registrar en el log que hemos creado el archivo correctamente
        \Log::info('Archivo Excel creado correctamente en: ' . $tempFile);

        return $tempFile;
    }
}
