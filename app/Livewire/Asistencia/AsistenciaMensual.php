<?php

namespace App\Livewire\Asistencia;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Grupo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AsistenciaMensual extends Component
{
    public $mes;
    public $anio;
    public $grupo_id;
    public $grupos;
    public $diasDelMes = [];
    public $diasNoLaborables = [];
    public $asistencias = [];
    public $alumnos = [];
    public $estadisticas = [];
    public $editandoNoLaborables = false;

    public function mount()
    {
        // Inicializar con el mes y año actual
        $fecha = Carbon::now();
        $this->mes = $fecha->month;
        $this->anio = $fecha->year;

        // Cargar grupos disponibles
        $this->grupos = Grupo::all();

        // Si hay grupos, seleccionar el primero por defecto
        if ($this->grupos->isNotEmpty()) {
            $this->grupo_id = $this->grupos->first()->id;
        }

        $this->cargarDiasDelMes();
        $this->cargarAsistencias();
    }

    public function cargarDiasDelMes()
    {
        $this->diasDelMes = [];
        $fecha = Carbon::createFromDate($this->anio, $this->mes, 1);
        $diasEnMes = $fecha->daysInMonth;

        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $fechaDia = Carbon::createFromDate($this->anio, $this->mes, $dia);
            $this->diasDelMes[] = [
                'numero' => $dia,
                'dia_semana' => $fechaDia->dayOfWeek,
                'es_fin_semana' => $fechaDia->isWeekend(),
                'fecha' => $fechaDia->format('Y-m-d')
            ];
        }

        // Por defecto, marcar fines de semana como no laborables
        $this->diasNoLaborables = collect($this->diasDelMes)
            ->filter(function($dia) {
                return $dia['es_fin_semana'];
            })
            ->pluck('fecha')
            ->toArray();
    }

    public function cambiarMes($incremento)
    {
        $fecha = Carbon::createFromDate($this->anio, $this->mes, 1);
        if ($incremento > 0) {
            $fecha->addMonths($incremento);
        } else {
            $fecha->subMonths(abs($incremento));
        }

        $this->mes = $fecha->month;
        $this->anio = $fecha->year;

        $this->cargarDiasDelMes();
        $this->cargarAsistencias();
    }

    public function updatedGrupoId()
    {
        $this->cargarAsistencias();
    }

    public function cargarAsistencias()
    {
        if (empty($this->grupo_id)) {
            return;
        }

        // Obtener alumnos del grupo
        $this->alumnos = Alumno::where('grupo_id', $this->grupo_id)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        // Fechas del mes
        $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1)->format('Y-m-d');
        $fechaFin = Carbon::createFromDate($this->anio, $this->mes, count($this->diasDelMes))->format('Y-m-d');

        // Obtener todas las asistencias del mes para este grupo
        $asistenciasDB = Asistencia::whereIn('alumno_id', $this->alumnos->pluck('id'))
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        // Inicializar asistencias vacías para todos los alumnos y días
        $this->asistencias = [];

        foreach ($this->alumnos as $alumno) {
            $this->asistencias[$alumno->id] = [];

            foreach ($this->diasDelMes as $dia) {
                $fecha = $dia['fecha'];

                // Buscar si existe una asistencia para este alumno y fecha
                $asistencia = $asistenciasDB->first(function ($item) use ($alumno, $fecha) {
                    return $item->alumno_id == $alumno->id && $item->fecha->format('Y-m-d') == $fecha;
                });

                $estado = 'falta'; // Por defecto, no hay asistencia

                if ($asistencia) {
                    $estado = $asistencia->estado_normalizado;
                }

                $this->asistencias[$alumno->id][$fecha] = $estado;
            }
        }

        $this->calcularEstadisticas();
    }

    public function guardarAsistencia($alumno_id, $fecha, $estado)
    {
        // Verificar si es un día no laborable
        if (in_array($fecha, $this->diasNoLaborables)) {
            return;
        }

        // Buscar asistencia existente o crear una nueva
        $asistencia = Asistencia::firstOrNew([
            'alumno_id' => $alumno_id,
            'fecha' => $fecha
        ]);

        // Configurar campos según el estado
        switch ($estado) {
            case 'asistio':
                $asistencia->estado = 'asistio';
                $asistencia->asistio = true;
                break;

            case 'falta':
                $asistencia->estado = 'falta';
                $asistencia->asistio = false;
                $asistencia->justificacion = null;
                break;

            case 'justificada':
                $asistencia->estado = 'justificada';
                $asistencia->asistio = false;
                $asistencia->justificacion = 'Justificada desde sistema de asistencia mensual';
                break;
        }

        // Guardar asistencia
        $asistencia->user_id = auth()->id(); // Usuario actual
        $asistencia->save();

        // Actualizar array de asistencias
        $this->asistencias[$alumno_id][$fecha] = $estado;

        // Recalcular estadísticas
        $this->calcularEstadisticas();

        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Asistencia actualizada correctamente'
        ]);
    }

    public function toggleDiaNoLaborable($fecha)
    {
        if (!$this->editandoNoLaborables) {
            return;
        }

        if (in_array($fecha, $this->diasNoLaborables)) {
            $this->diasNoLaborables = array_diff($this->diasNoLaborables, [$fecha]);
        } else {
            $this->diasNoLaborables[] = $fecha;
        }

        $this->calcularEstadisticas();
    }

    public function toggleEdicionNoLaborables()
    {
        $this->editandoNoLaborables = !$this->editandoNoLaborables;
    }

    public function calcularEstadisticas()
    {
        $this->estadisticas = [];

        foreach ($this->alumnos as $alumno) {
            $totalDias = 0;
            $asistencias = 0;
            $inasistencias = 0;
            $justificadas = 0;

            foreach ($this->diasDelMes as $dia) {
                $fecha = $dia['fecha'];

                // No contar días no laborables
                if (in_array($fecha, $this->diasNoLaborables)) {
                    continue;
                }

                $totalDias++;

                $estado = $this->asistencias[$alumno->id][$fecha] ?? 'falta';

                if ($estado == 'asistio') {
                    $asistencias++;
                } elseif ($estado == 'justificada') {
                    $justificadas++;
                } else {
                    $inasistencias++;
                }
            }

            // Evitar división por cero
            $porcentajeAsistencia = $totalDias > 0 ? round(($asistencias / $totalDias) * 100, 2) : 0;
            $porcentajeInasistencia = $totalDias > 0 ? round(($inasistencias / $totalDias) * 100, 2) : 0;

            $this->estadisticas[$alumno->id] = [
                'total_dias' => $totalDias,
                'asistencias' => $asistencias,
                'inasistencias' => $inasistencias,
                'justificadas' => $justificadas,
                'porcentaje_asistencia' => $porcentajeAsistencia,
                'porcentaje_inasistencia' => $porcentajeInasistencia
            ];
        }
    }

    public function getNombreMesProperty()
    {
        return ucfirst(Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName);
    }

    public function render()
    {
        return view('livewire.asistencia.asistencia-mensual');
    }
}
