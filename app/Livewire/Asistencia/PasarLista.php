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
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class PasarLista extends Component
{
    use WithPagination;

    public $fecha;
    public $grupo_id;
    public $search = '';
    public $asistencias = [];
    public $asistenciasOriginales = [];
    public $configuracionMes;
    public $hayCambiosPendientes = false;

    protected $rules = [
        'asistencias.*.estado' => 'required|in:asistio,falta,justificada',
        'asistencias.*.observaciones' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'asistencias.*.estado.required' => 'El estado de asistencia es obligatorio',
        'asistencias.*.estado.in' => 'El estado debe ser: asistió, falta o justificada',
        'asistencias.*.observaciones.max' => 'Las observaciones no deben exceder los 255 caracteres',
    ];

    public function mount()
    {
        $this->fecha = Carbon::now()->format('Y-m-d');
        $this->cargarConfiguracionMes();
    }

    public function updatedFecha()
    {
        $this->resetPage();
        $this->cargarAsistencias();
        $this->cargarConfiguracionMes();
    }

    public function updatedGrupoId()
    {
        $this->resetPage();
        $this->cargarAsistencias();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function cargarConfiguracionMes()
    {
        $fecha = Carbon::parse($this->fecha);
        $this->configuracionMes = ConfiguracionAsistencia::where('user_id', Auth::id())
            ->where('mes', $fecha->month)
            ->where('anio', $fecha->year)
            ->first();
    }

    public function cargarAsistencias()
    {
        $this->hayCambiosPendientes = false;
        $alumnos = $this->obtenerAlumnos();

        $this->asistencias = [];
        $fecha = $this->fecha;

        foreach ($alumnos as $alumno) {
            // Consultamos el registro de asistencia de forma explícita
            $asistencia = Asistencia::where('alumno_id', $alumno->id)
                ->where('fecha', $fecha)
                ->where('user_id', Auth::id())
                ->first();

            if (!$asistencia) {
                $this->asistencias[$alumno->id] = [
                    'alumno_id' => $alumno->id,
                    'nombre_completo' => $alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombre,
                    'estado' => 'asistio',
                    'observaciones' => '',
                    'existe' => false
                ];
            } else {
                // Corregir el problema usando directamente los valores de la base de datos
                // en lugar de usar el getter/accessor
                $estadoDirecto = $asistencia->getAttribute('estado');

                $this->asistencias[$alumno->id] = [
                    'id' => $asistencia->id,
                    'alumno_id' => $alumno->id,
                    'nombre_completo' => $alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombre,
                    'estado' => $estadoDirecto, // Usar el valor directo del campo
                    'observaciones' => $asistencia->observaciones ?? '',
                    'existe' => true
                ];
            }
        }

        // Guardar una copia de las asistencias originales para comparación
        $this->asistenciasOriginales = json_encode($this->asistencias);
    }

    public function marcarTodos($estado)
    {
        foreach ($this->asistencias as $alumno_id => $asistencia) {
            $this->asistencias[$alumno_id]['estado'] = $estado;
        }
        $this->verificarCambios();
    }

    public function cambiarEstado($alumno_id, $estado)
    {
        $this->asistencias[$alumno_id]['estado'] = $estado;
        $this->verificarCambios();
    }

    public function verificarCambios()
    {
        $this->hayCambiosPendientes = json_encode($this->asistencias) !== $this->asistenciasOriginales;
    }

    public function guardarAsistencias()
    {
        $this->validate();

        $fecha = $this->fecha;
        $user_id = Auth::id();
        $alumnosModificados = [];

        foreach ($this->asistencias as $alumno_id => $asistencia) {
            // Determinar el valor de 'asistio' basado en el estado
            $asistioValue = ($asistencia['estado'] === 'asistio') ? 1 : 0;
            $estado = $asistencia['estado']; // Guardamos explícitamente el valor de estado para descartar problemas de referencia

            // Para la columna justificacion
            $justificacion = null;
            if ($asistencia['estado'] === 'justificada') {
                $justificacion = $asistencia['observaciones'] ?: 'Justificada';
            }

            // Almacenamos los IDs que modificamos para verificarlos después
            $alumnosModificados[] = $alumno_id;

            if ($asistencia['existe']) {
                // Actualizar registro existente de manera directa mediante query builder para evitar cualquier problema con el modelo
                DB::table('asistencias')
                    ->where('id', $asistencia['id'])
                    ->update([
                        'estado' => $estado,
                        'asistio' => $asistioValue,
                        'justificacion' => $justificacion,
                        'observaciones' => $asistencia['observaciones'],
                        'updated_at' => now()
                    ]);
            } else {
                // Crear nuevo registro directamente
                DB::table('asistencias')->insert([
                    'alumno_id' => $alumno_id,
                    'user_id' => $user_id,
                    'fecha' => $fecha,
                    'estado' => $estado,
                    'asistio' => $asistioValue,
                    'justificacion' => $justificacion,
                    'observaciones' => $asistencia['observaciones'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Forzamos una recarga completa de los datos desde la base de datos
        $this->cargarAsistencias();
        $this->dispatch('notify', [
            'message' => 'Asistencias guardadas correctamente',
            'type' => 'success'
        ]);
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

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function esDiaHabil()
    {
        if (!$this->configuracionMes) {
            return false;
        }

        if ($this->configuracionMes->es_periodo_vacacional) {
            return false;
        }

        $fecha = Carbon::parse($this->fecha);
        $diaSemana = $fecha->dayOfWeek;

        // Si es sábado (6) o domingo (0), no es día hábil
        if ($diaSemana == 0 || $diaSemana == 6) {
            return false;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.asistencia.pasar-lista', [
            'grupos' => Grupo::orderBy('nombre')->get(),
            'esDiaHabil' => $this->esDiaHabil(),
            'configuracionExiste' => (bool) $this->configuracionMes,
        ]);
    }
}
