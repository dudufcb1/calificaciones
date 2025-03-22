<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AsistenciaCamposFormativosExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithTitle,
    WithColumnWidths,
    WithCustomStartCell,
    ShouldAutoSize
{
    protected $alumnos;
    protected $diasDelMes;
    protected $diasNoLaborables;
    protected $asistencias;
    protected $estadisticas;
    protected $camposFormativos;
    protected $camposFormativosPorDia;
    protected $estadisticasPorCampoFormativo;
    protected $mes;
    protected $anio;
    protected $nombreGrupo;
    protected $cicloEscolar;

    public function __construct(
        $alumnos,
        $diasDelMes,
        $diasNoLaborables,
        $asistencias,
        $estadisticas,
        $camposFormativos,
        $camposFormativosPorDia,
        $estadisticasPorCampoFormativo,
        $mes,
        $anio,
        $nombreGrupo,
        $cicloEscolar
    ) {
        $this->alumnos = $alumnos;
        $this->diasDelMes = $diasDelMes;
        $this->diasNoLaborables = $diasNoLaborables;
        $this->asistencias = $asistencias;
        $this->estadisticas = $estadisticas;
        $this->camposFormativos = $camposFormativos;
        $this->camposFormativosPorDia = $camposFormativosPorDia;
        $this->estadisticasPorCampoFormativo = $estadisticasPorCampoFormativo;
        $this->mes = $mes;
        $this->anio = $anio;
        $this->nombreGrupo = $nombreGrupo;
        $this->cicloEscolar = $cicloEscolar;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        return "Asistencia {$this->mes} {$this->anio}";
    }

    public function headings(): array
    {
        $headings = [
            ['REPORTE DE ASISTENCIA POR CAMPOS FORMATIVOS'],
            ["Ciclo Escolar: {$this->cicloEscolar}", '', "Grupo: {$this->nombreGrupo}", '', "Mes: {$this->mes} {$this->anio}"],
            [''], // Espacio en blanco
            ['CAMPOS FORMATIVOS TRABAJADOS EN EL MES'],
        ];

        // Agregar encabezado con los campos formativos
        $camposRow = ['Fecha'];

        foreach ($this->camposFormativos as $campo) {
            $camposRow[] = $campo->nombre;
        }

        $headings[] = $camposRow;

        // Agregar información de qué días se trabajaron qué campos
        foreach ($this->diasDelMes as $dia) {
            $fecha = $dia['fecha'];

            // No incluir días no laborables
            if (in_array($fecha, $this->diasNoLaborables)) {
                continue;
            }

            $camposPorDia = $this->camposFormativosPorDia[$fecha] ?? [];

            if (empty($camposPorDia)) {
                continue;
            }

            $row = [date('d/m/Y', strtotime($fecha))];

            foreach ($this->camposFormativos as $campo) {
                if (in_array($campo->id, $camposPorDia)) {
                    $row[] = 'X';
                } else {
                    $row[] = '';
                }
            }

            $headings[] = $row;
        }

        // Agregar espacios en blanco
        $headings[] = [''];
        $headings[] = [''];

        // Encabezado principal para la tabla de asistencias
        $headings[] = ['ASISTENCIA GENERAL'];

        // Encabezado para la tabla de asistencias
        $encabezadoAsistencias = ['No.', 'Nombre', 'Apellidos'];

        // Agregar columnas para cada día
        foreach ($this->diasDelMes as $dia) {
            if (!in_array($dia['fecha'], $this->diasNoLaborables)) {
                $encabezadoAsistencias[] = $dia['numero'];
            }
        }

        // Agregar columnas de estadísticas
        $encabezadoAsistencias[] = 'Asistencias';
        $encabezadoAsistencias[] = '%';
        $encabezadoAsistencias[] = 'Faltas';
        $encabezadoAsistencias[] = '%';

        $headings[] = $encabezadoAsistencias;

        return $headings;
    }

    public function collection()
    {
        $rows = new Collection();

        // Filas para cada alumno
        $contador = 1;
        foreach ($this->alumnos as $alumno) {
            $row = [
                $contador,
                $alumno->nombre,
                $alumno->apellido_paterno . ' ' . $alumno->apellido_materno
            ];

            // Agregar columnas para cada día
            foreach ($this->diasDelMes as $dia) {
                $fecha = $dia['fecha'];

                // No incluir días no laborables
                if (in_array($fecha, $this->diasNoLaborables)) {
                    continue;
                }

                $estado = $this->asistencias[$alumno->id][$fecha] ?? 'falta';

                if ($estado === 'asistio') {
                    $row[] = '✓';
                } elseif ($estado === 'justificada') {
                    $row[] = 'J';
                } else {
                    $row[] = 'F';
                }
            }

            // Agregar estadísticas
            if (isset($this->estadisticas[$alumno->id])) {
                $row[] = $this->estadisticas[$alumno->id]['asistencias'];
                $row[] = $this->estadisticas[$alumno->id]['porcentaje_asistencia'] . '%';
                $row[] = $this->estadisticas[$alumno->id]['inasistencias'];
                $row[] = $this->estadisticas[$alumno->id]['porcentaje_inasistencia'] . '%';
            } else {
                $row[] = 0;
                $row[] = '0%';
                $row[] = 0;
                $row[] = '0%';
            }

            $rows->push($row);
            $contador++;
        }

        // Agregar espacios en blanco
        $rows->push(['']);
        $rows->push(['']);

        // Agregar tabla para cada campo formativo
        foreach ($this->camposFormativos as $campo) {
            // Verificar si hay asistencias registradas para este campo
            $hayCampo = false;
            foreach ($this->diasDelMes as $dia) {
                $fecha = $dia['fecha'];
                $camposPorDia = $this->camposFormativosPorDia[$fecha] ?? [];
                if (in_array($campo->id, $camposPorDia)) {
                    $hayCampo = true;
                    break;
                }
            }

            if (!$hayCampo) {
                continue;
            }

            // Encabezado para el campo formativo
            $rows->push(["CAMPO FORMATIVO: {$campo->nombre}"]);

            // Encabezados de columnas
            $encabezadoCampo = ['No.', 'Nombre', 'Apellidos'];

            // Días con este campo formativo
            $diasConEsteCampo = [];
            foreach ($this->diasDelMes as $dia) {
                $fecha = $dia['fecha'];

                // No incluir días no laborables
                if (in_array($fecha, $this->diasNoLaborables)) {
                    continue;
                }

                $camposPorDia = $this->camposFormativosPorDia[$fecha] ?? [];

                if (in_array($campo->id, $camposPorDia)) {
                    $encabezadoCampo[] = $dia['numero'];
                    $diasConEsteCampo[] = $fecha;
                }
            }

            // Estadísticas
            $encabezadoCampo[] = 'Asistencias';
            $encabezadoCampo[] = '%';
            $encabezadoCampo[] = 'Faltas';
            $encabezadoCampo[] = '%';

            $rows->push($encabezadoCampo);

            // Datos de alumnos para este campo
            $contador = 1;
            foreach ($this->alumnos as $alumno) {
                $row = [
                    $contador,
                    $alumno->nombre,
                    $alumno->apellido_paterno . ' ' . $alumno->apellido_materno
                ];

                // Agregar asistencias para los días con este campo
                foreach ($diasConEsteCampo as $fecha) {
                    $estado = $this->asistencias[$alumno->id][$fecha] ?? 'falta';

                    if ($estado === 'asistio') {
                        $row[] = '✓';
                    } elseif ($estado === 'justificada') {
                        $row[] = 'J';
                    } else {
                        $row[] = 'F';
                    }
                }

                // Agregar estadísticas por campo
                if (isset($this->estadisticasPorCampoFormativo[$alumno->id][$campo->id])) {
                    $estadCampo = $this->estadisticasPorCampoFormativo[$alumno->id][$campo->id];
                    $row[] = $estadCampo['asistencias'];
                    $row[] = $estadCampo['porcentaje_asistencia'] . '%';
                    $row[] = $estadCampo['inasistencias'];
                    $row[] = $estadCampo['porcentaje_inasistencia'] . '%';
                } else {
                    $row[] = 0;
                    $row[] = '0%';
                    $row[] = 0;
                    $row[] = '0%';
                }

                $rows->push($row);
                $contador++;
            }

            // Espacio después de cada campo
            $rows->push(['']);
        }

        // Leyenda
        $rows->push(['LEYENDA:']);
        $rows->push(['✓ - Asistencia', '', 'F - Falta', '', 'J - Justificada']);

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,     // No.
            'B' => 20,    // Nombre
            'C' => 30,    // Apellidos
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Estilo para el título principal
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Estilo para la información del ciclo, grupo y mes
        $sheet->getStyle('A2:E2')->getFont()->setBold(true);

        // Estilo para el encabezado "CAMPOS FORMATIVOS TRABAJADOS EN EL MES"
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells("A4:{$lastColumn}4");
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Estilo para el encabezado de la tabla de campos formativos
        $encabezadoCamposRow = 5;
        $sheet->getStyle("A{$encabezadoCamposRow}:{$lastColumn}{$encabezadoCamposRow}")
            ->getFont()->setBold(true);
        $sheet->getStyle("A{$encabezadoCamposRow}:{$lastColumn}{$encabezadoCamposRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');

        // Buscar la fila donde está "ASISTENCIA GENERAL"
        $asistenciaGeneralRow = null;
        for ($i = 6; $i <= $lastRow; $i++) {
            if ($sheet->getCell("A{$i}")->getValue() === 'ASISTENCIA GENERAL') {
                $asistenciaGeneralRow = $i;
                break;
            }
        }

        if ($asistenciaGeneralRow) {
            // Estilo para el encabezado "ASISTENCIA GENERAL"
            $sheet->getStyle("A{$asistenciaGeneralRow}")->getFont()->setBold(true)->setSize(12);
            $sheet->mergeCells("A{$asistenciaGeneralRow}:{$lastColumn}{$asistenciaGeneralRow}");
            $sheet->getStyle("A{$asistenciaGeneralRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Estilo para el encabezado de la tabla de asistencias
            $encabezadoAsistenciasRow = $asistenciaGeneralRow + 1;
            $sheet->getStyle("A{$encabezadoAsistenciasRow}:{$lastColumn}{$encabezadoAsistenciasRow}")
                ->getFont()->setBold(true);
            $sheet->getStyle("A{$encabezadoAsistenciasRow}:{$lastColumn}{$encabezadoAsistenciasRow}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDDDDD');
        }

        // Estilo para los encabezados de campos formativos individuales
        for ($i = 1; $i <= $lastRow; $i++) {
            $cellValue = $sheet->getCell("A{$i}")->getValue();
            if (strpos($cellValue, 'CAMPO FORMATIVO:') === 0) {
                $sheet->getStyle("A{$i}")->getFont()->setBold(true);
                $sheet->mergeCells("A{$i}:{$lastColumn}{$i}");
                $sheet->getStyle("A{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$i}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E6F7FF');

                // Estilo para encabezado de tabla
                $encabezadoRow = $i + 1;
                $sheet->getStyle("A{$encabezadoRow}:{$lastColumn}{$encabezadoRow}")
                    ->getFont()->setBold(true);
                $sheet->getStyle("A{$encabezadoRow}:{$lastColumn}{$encabezadoRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('DDDDDD');
            }
        }

        // Estilo para la leyenda
        for ($i = $lastRow - 1; $i <= $lastRow; $i++) {
            $cellValue = $sheet->getCell("A{$i}")->getValue();
            if ($cellValue === 'LEYENDA:') {
                $sheet->getStyle("A{$i}")->getFont()->setBold(true);
            }
        }

        // Bordes para todas las celdas con datos
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Centrar las celdas de días (números)
        for ($i = 1; $i <= $lastRow; $i++) {
            for ($col = 'D'; $col <= $lastColumn; $col++) {
                $sheet->getStyle("{$col}{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        return [];
    }
}
