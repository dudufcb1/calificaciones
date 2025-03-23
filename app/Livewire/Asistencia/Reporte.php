<?php

namespace App\Livewire\Asistencia;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\ConfiguracionAsistencia;
use App\Models\Grupo;
use App\Services\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Reporte extends Component
{
    use WithPagination;

    public $mes;
    public $anio;
    public $grupo_id;
    public $alumno_id;
    public $tipoReporte = 'mes';
    public $fechaInicio;
    public $fechaFin;
    public $search = '';

    public function mount()
    {
        $this->mes = Carbon::now()->month;
        $this->anio = Carbon::now()->year;

        // Inicializar fechas para el mes actual
        $fechaActual = Carbon::create($this->anio, $this->mes, 1);
        $this->fechaInicio = $fechaActual->copy()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = $fechaActual->copy()->endOfMonth()->format('Y-m-d');
    }

    public function updatedTipoReporte()
    {
        if ($this->tipoReporte === 'mes') {
            $fechaActual = Carbon::create($this->anio, $this->mes, 1);
            $this->fechaInicio = $fechaActual->copy()->startOfMonth()->format('Y-m-d');
            $this->fechaFin = $fechaActual->copy()->endOfMonth()->format('Y-m-d');
        } else {
            // Para tipo personalizado, dejamos las fechas como están
            // o podríamos resetearlas a un rango predeterminado
        }
    }

    public function updatedMes()
    {
        if ($this->tipoReporte === 'mes') {
            $fechaActual = Carbon::create($this->anio, $this->mes, 1);
            $this->fechaInicio = $fechaActual->copy()->startOfMonth()->format('Y-m-d');
            $this->fechaFin = $fechaActual->copy()->endOfMonth()->format('Y-m-d');
        }
    }

    public function updatedAnio()
    {
        if ($this->tipoReporte === 'mes') {
            $fechaActual = Carbon::create($this->anio, $this->mes, 1);
            $this->fechaInicio = $fechaActual->copy()->startOfMonth()->format('Y-m-d');
            $this->fechaFin = $fechaActual->copy()->endOfMonth()->format('Y-m-d');
        }
    }

    public function obtenerConfiguracionMes()
    {
        return ConfiguracionAsistencia::where('user_id', Auth::id())
            ->where('mes', $this->mes)
            ->where('anio', $this->anio)
            ->first();
    }

    public function obtenerAlumnos()
    {
        $query = Alumno::query()
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre');

        if ($this->grupo_id) {
            $query->where('grupo_id', $this->grupo_id);
        }

        if ($this->alumno_id) {
            $query->where('id', $this->alumno_id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function obtenerAsistencias($alumno_id)
    {
        return Asistencia::where('alumno_id', $alumno_id)
            ->where('user_id', Auth::id())
            ->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
            ->get();
    }

    private function calcularEstadisticas($alumno_id)
    {
        $asistenciaService = new AsistenciaService();

        // Determinar el rango de fechas según el tipo de reporte
        if ($this->tipoReporte === 'mes') {
            $fechaInicio = Carbon::create($this->anio, $this->mes, 1)->format('Y-m-d');
            $fechaFin = Carbon::create($this->anio, $this->mes, 1)->endOfMonth()->format('Y-m-d');
        } else {
            $fechaInicio = $this->fechaInicio;
            $fechaFin = $this->fechaFin;
        }

        // Obtener configuración de días no laborables (aquí podrías adaptar según tu lógica)
        $diasNoLaborables = [];
        $configuracionMes = $this->obtenerConfiguracionMes();
        if ($configuracionMes && $configuracionMes->es_periodo_vacacional) {
            // Si es periodo vacacional, marcar todos los días como no laborables
            $fechaActual = Carbon::parse($fechaInicio);
            while ($fechaActual->lte(Carbon::parse($fechaFin))) {
                $diasNoLaborables[] = $fechaActual->format('Y-m-d');
                $fechaActual->addDay();
            }
        } else {
            // Considerar fines de semana como no laborables por defecto
            $fechaActual = Carbon::parse($fechaInicio);
            while ($fechaActual->lte(Carbon::parse($fechaFin))) {
                if ($fechaActual->isWeekend()) {
                    $diasNoLaborables[] = $fechaActual->format('Y-m-d');
                }
                $fechaActual->addDay();
            }
        }

        // Obtener estadísticas usando el servicio
        $resultados = $asistenciaService->calcularEstadisticas(
            $alumno_id,
            $fechaInicio,
            $fechaFin,
            $diasNoLaborables
        );

        // Formatear resultado para mantener compatibilidad con el código existente
        if (isset($resultados[$alumno_id])) {
            return [
                'total_dias' => $resultados[$alumno_id]['total_dias'],
                'total_asistencias' => $resultados[$alumno_id]['asistencias'],
                'total_faltas' => $resultados[$alumno_id]['inasistencias'],
                'total_justificadas' => $resultados[$alumno_id]['justificadas'],
                'porcentaje_asistencia' => $resultados[$alumno_id]['porcentaje_asistencia'],
            ];
        }

        // En caso de no encontrar resultados, devolver valores predeterminados
        return [
            'total_dias' => 0,
            'total_asistencias' => 0,
            'total_faltas' => 0,
            'total_justificadas' => 0,
            'porcentaje_asistencia' => 0,
        ];
    }

    public function render()
    {
        $alumnos = $this->obtenerAlumnos();
        $configuracionMes = $this->obtenerConfiguracionMes();

        $asistenciaService = new AsistenciaService();
        $reporteData = [];

        if (count($alumnos) > 0) {
            // Determinar rango de fechas
            if ($this->tipoReporte === 'mes') {
                $fechaInicio = Carbon::create($this->anio, $this->mes, 1)->format('Y-m-d');
                $fechaFin = Carbon::create($this->anio, $this->mes, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $fechaInicio = $this->fechaInicio;
                $fechaFin = $this->fechaFin;
            }

            // Obtener días no laborables
            $diasNoLaborables = [];
            if ($configuracionMes && $configuracionMes->es_periodo_vacacional) {
                $fechaActual = Carbon::parse($fechaInicio);
                while ($fechaActual->lte(Carbon::parse($fechaFin))) {
                    $diasNoLaborables[] = $fechaActual->format('Y-m-d');
                    $fechaActual->addDay();
                }
            } else {
                $fechaActual = Carbon::parse($fechaInicio);
                while ($fechaActual->lte(Carbon::parse($fechaFin))) {
                    if ($fechaActual->isWeekend()) {
                        $diasNoLaborables[] = $fechaActual->format('Y-m-d');
                    }
                    $fechaActual->addDay();
                }
            }

            // Obtener estadísticas para todos los alumnos de una sola vez
            $alumnoIds = $alumnos->pluck('id')->toArray();
            $estadisticasAlumnos = $asistenciaService->calcularEstadisticas(
                $alumnoIds,
                $fechaInicio,
                $fechaFin,
                $diasNoLaborables
            );

            foreach ($alumnos as $alumno) {
                if (isset($estadisticasAlumnos[$alumno->id])) {
                    $datos = $estadisticasAlumnos[$alumno->id];
                    $reporteData[] = [
                        'alumno' => $alumno,
                        'estadisticas' => [
                            'total_dias' => $datos['total_dias'],
                            'total_asistencias' => $datos['asistencias'],
                            'total_faltas' => $datos['inasistencias'],
                            'total_justificadas' => $datos['justificadas'],
                            'porcentaje_asistencia' => $datos['porcentaje_asistencia'],
                        ]
                    ];
                } else {
                    // Datos por defecto si no hay estadísticas
                    $reporteData[] = [
                        'alumno' => $alumno,
                        'estadisticas' => [
                            'total_dias' => 0,
                            'total_asistencias' => 0,
                            'total_faltas' => 0,
                            'total_justificadas' => 0,
                            'porcentaje_asistencia' => 0,
                        ]
                    ];
                }
            }
        }

        return view('livewire.asistencia.reporte', [
            'reporteData' => $reporteData,
            'grupos' => Grupo::orderBy('nombre')->get(),
            'alumnos' => Alumno::orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombre')
                ->get(),
            'configuracionMes' => $configuracionMes,
            'periodoTexto' => $this->tipoReporte === 'mes'
                ? 'Período: ' . Carbon::create($this->anio, $this->mes, 1)->translatedFormat('F Y')
                : 'Período: ' . Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($this->fechaFin)->format('d/m/Y'),
        ]);
    }
}
