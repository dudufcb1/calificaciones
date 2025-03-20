<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CreateExcelTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:create-template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear una plantilla de Excel para exportación de evaluaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creando plantilla de Excel...');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla de Evaluación');

        // Establecer anchos de columna
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Título
        $sheet->setCellValue('A1', 'REPORTE DE EVALUACIÓN');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Información básica - No incluimos placeholders literales
        $sheet->setCellValue('A3', 'Título:');
        $sheet->setCellValue('B3', '');
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->setCellValue('A4', 'Campo Formativo:');
        $sheet->setCellValue('B4', '');
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->setCellValue('A5', 'Fecha:');
        $sheet->setCellValue('B5', '');
        $sheet->getStyle('A5')->getFont()->setBold(true);

        $sheet->setCellValue('A6', 'Descripción:');
        $sheet->setCellValue('B6', '');
        $sheet->getStyle('A6')->getFont()->setBold(true);

        // Sección de Criterios
        $sheet->setCellValue('A8', 'CRITERIOS DE EVALUACIÓN');
        $sheet->mergeCells('A8:C8');
        $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        // Encabezados de criterios
        $sheet->setCellValue('A9', 'Criterio');
        $sheet->setCellValue('B9', 'Descripción');
        $sheet->setCellValue('C9', 'Porcentaje');
        $sheet->getStyle('A9:C9')->getFont()->setBold(true);
        $sheet->getStyle('A9:C9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
        $sheet->getStyle('A9:C9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Dejar filas para criterios (vacías para ser llenadas dinámicamente)
        for ($i = 10; $i <= 14; $i++) {
            $sheet->setCellValue('A' . $i, '');
            $sheet->setCellValue('B' . $i, '');
            $sheet->setCellValue('C' . $i, '');
            $sheet->getStyle('A' . $i . ':C' . $i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Sección de Alumnos
        $sheet->setCellValue('A14', 'ALUMNOS EVALUADOS');
        $sheet->mergeCells('A14:F14');
        $sheet->getStyle('A14')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A14')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');

        // Encabezados de alumnos (se llenarán dinámicamente)
        $sheet->setCellValue('A15', 'Alumno');
        $sheet->setCellValue('B15', '');  // Para Criterio 1
        $sheet->setCellValue('C15', '');  // Para Criterio 2
        $sheet->setCellValue('D15', '');  // Para Criterio 3
        $sheet->setCellValue('E15', '');  // Para Criterio 4
        $sheet->setCellValue('F15', 'Promedio');
        $sheet->getStyle('A15:F15')->getFont()->setBold(true);
        $sheet->getStyle('A15:F15')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
        $sheet->getStyle('A15:F15')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Dejar filas para alumnos (vacías para ser llenadas dinámicamente)
        for ($i = 16; $i <= 25; $i++) {
            $sheet->setCellValue('A' . $i, '');
            $sheet->setCellValue('B' . $i, '');
            $sheet->setCellValue('C' . $i, '');
            $sheet->setCellValue('D' . $i, '');
            $sheet->setCellValue('E' . $i, '');
            $sheet->setCellValue('F' . $i, '');
            $sheet->getStyle('A' . $i . ':F' . $i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Pie de página
        $sheet->setCellValue('A27', '');  // Se llenará con la fecha al exportar
        $sheet->mergeCells('A27:F27');
        $sheet->getStyle('A27')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A27')->getFont()->setItalic(true);

        // Configuración adicional
        // Añadir un estilo suave para alternar filas
        for ($i = 16; $i <= 25; $i += 2) {
            $sheet->getStyle('A' . $i . ':F' . $i)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9F9F9');
        }

        // Guardar el archivo
        $templatePath = storage_path('app/templates/evaluacion_template.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($templatePath);

        $this->info('Plantilla creada exitosamente en: ' . $templatePath);
        $this->info('Para editar la plantilla, abra este archivo con Excel y modifique su diseño según necesite.');
        $this->info('Los datos serán insertados automáticamente en las ubicaciones correspondientes al exportar.');

        return Command::SUCCESS;
    }
}
