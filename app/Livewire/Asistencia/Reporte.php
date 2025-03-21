<?php

namespace App\Livewire\Asistencia;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\ConfiguracionAsistencia;
use App\Models\Grupo;
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

    public function calcularEstadisticas($alumno_id)
    {
        $asistencias = $this->obtenerAsistencias($alumno_id);

        $totalDias = $asistencias->count();
        $totalAsistencias = $asistencias->where('estado', 'asistio')->count();
        $totalFaltas = $asistencias->where('estado', 'falta')->count();
        $totalJustificadas = $asistencias->where('estado', 'justificada')->count();

        $porcentajeAsistencia = $totalDias > 0
            ? round(($totalAsistencias / $totalDias) * 100, 2)
            : 0;

        return [
            'total_dias' => $totalDias,
            'total_asistencias' => $totalAsistencias,
            'total_faltas' => $totalFaltas,
            'total_justificadas' => $totalJustificadas,
            'porcentaje_asistencia' => $porcentajeAsistencia,
        ];
    }

    public function render()
    {
        $alumnos = $this->obtenerAlumnos();
        $configuracionMes = $this->obtenerConfiguracionMes();

        $reporteData = [];

        foreach ($alumnos as $alumno) {
            $estadisticas = $this->calcularEstadisticas($alumno->id);

            $reporteData[] = [
                'alumno' => $alumno,
                'estadisticas' => $estadisticas,
            ];
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
