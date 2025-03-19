<?php

namespace App\Livewire\Evaluacion;

use App\Models\Alumno;
use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\Evaluacion;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public $evaluacionId;
    public $campoFormativoId;
    public $alumnoId;
    public $nombreAlumno;
    public $criterios = [];
    public $calificaciones = [];
    public $promedioFinal = 0;
    public $alumnoSearch = '';
    public $alumnosSugeridos = [];
    public $showCreateAlumno = false;
    public $editing = false;
    public $autoSaveMessage = '';
    public $nuevoAlumno = [
        'nombre' => '',
        'apellido_paterno' => '',
        'apellido_materno' => '',
        'grupo_id' => null,
        'estado' => 'activo'
    ];

    protected $rules = [
        'campoFormativoId' => 'required|exists:campo_formativos,id',
        'alumnoId' => 'required|exists:alumnos,id',
        'calificaciones.*' => 'required|numeric|min:1|max:100',
        'nuevoAlumno.nombre' => 'required|string|max:255',
        'nuevoAlumno.apellido_paterno' => 'required|string|max:255',
        'nuevoAlumno.apellido_materno' => 'required|string|max:255'
    ];

    public function mount($evaluacionId = null)
    {
        $this->evaluacionId = $evaluacionId;
        if ($evaluacionId) {
            $this->editing = true;
            $this->loadEvaluacion();
        }
    }

    public function loadEvaluacion()
    {
        $evaluacion = Evaluacion::findOrFail($this->evaluacionId);
        $this->campoFormativoId = $evaluacion->campo_formativo_id;
        $this->alumnoId = $evaluacion->alumno_id;
        $this->nombreAlumno = $evaluacion->alumno->nombre_completo;
        $this->criterios = $evaluacion->criterios->toArray();
        $this->calificaciones = $evaluacion->criterios->pluck('pivot.calificacion', 'id')->toArray();
        $this->promedioFinal = $evaluacion->promedio_final;
    }

    public function updatedCampoFormativoId()
    {
        if ($this->campoFormativoId) {
            $this->criterios = Criterio::where('campo_formativo_id', $this->campoFormativoId)
                ->orderBy('orden')
                ->get()
                ->toArray();
            $this->calificaciones = [];
            $this->promedioFinal = 0;
        }
    }

    public function updatedCalificaciones()
    {
        $this->calcularPromedio();
        $this->autosave();
    }

    public function calcularPromedio()
    {
        if (empty($this->calificaciones) || empty($this->criterios)) {
            $this->promedioFinal = 0;
            return;
        }

        $sumaPonderada = 0;
        $sumaPesos = 0;

        foreach ($this->criterios as $criterio) {
            if (isset($criterio['id']) && isset($this->calificaciones[$criterio['id']])) {
                $sumaPonderada += $this->calificaciones[$criterio['id']] * ($criterio['porcentaje'] / 100);
                $sumaPesos += $criterio['porcentaje'] / 100;
            }
        }

        $this->promedioFinal = $sumaPesos > 0 ? round($sumaPonderada / $sumaPesos, 2) : 0;
    }

    public function buscarAlumno()
    {
        if (strlen($this->alumnoSearch) >= 3) {
            $this->alumnosSugeridos = Alumno::where(function($query) {
                    $query->where('nombre', 'like', '%' . $this->alumnoSearch . '%')
                          ->orWhere('apellido_paterno', 'like', '%' . $this->alumnoSearch . '%')
                          ->orWhere('apellido_materno', 'like', '%' . $this->alumnoSearch . '%');
                })
                ->where('estado', 'activo')
                ->orderBy('nombre')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->limit(5)
                ->get();
        } else {
            $this->alumnosSugeridos = [];
        }
    }

    public function seleccionarAlumno($alumnoId)
    {
        $alumno = Alumno::find($alumnoId);
        $this->alumnoId = $alumnoId;
        $this->nombreAlumno = $alumno->nombre_completo;
        $this->alumnoSearch = '';
        $this->alumnosSugeridos = [];
        $this->autosave();
    }

    public function toggleCreateAlumno()
    {
        $this->showCreateAlumno = !$this->showCreateAlumno;
        if (!$this->showCreateAlumno) {
            $this->reset('nuevoAlumno');
        }
    }

    public function crearAlumno()
    {
        $this->validate([
            'nuevoAlumno.nombre' => 'required|string|max:255',
            'nuevoAlumno.apellido_paterno' => 'required|string|max:255',
            'nuevoAlumno.apellido_materno' => 'required|string|max:255',
        ]);

        $alumno = Alumno::create($this->nuevoAlumno);
        $this->seleccionarAlumno($alumno->id);
        $this->toggleCreateAlumno();
        $this->reset('nuevoAlumno');
    }

    public function autosave()
    {
        if (!$this->campoFormativoId || !$this->alumnoId) {
            return;
        }

        $evaluacion = $this->editing
            ? Evaluacion::find($this->evaluacionId)
            : new Evaluacion();

        $evaluacion->campo_formativo_id = $this->campoFormativoId;
        $evaluacion->alumno_id = $this->alumnoId;
        $evaluacion->promedio_final = $this->promedioFinal;
        $evaluacion->is_draft = true;
        $evaluacion->save();

        if (!$this->editing) {
            $this->evaluacionId = $evaluacion->id;
            $this->editing = true;
        }

        foreach ($this->calificaciones as $criterioId => $calificacion) {
            $criterioIndex = collect($this->criterios)->search(function($item) use ($criterioId) {
                return $item['id'] == $criterioId;
            });

            if ($criterioIndex === false) continue;

            $evaluacion->criterios()->syncWithoutDetaching([
                $criterioId => [
                    'calificacion' => $calificacion,
                    'calificacion_ponderada' => $calificacion * ($this->criterios[$criterioIndex]['porcentaje'] / 100)
                ]
            ]);
        }

        $this->autoSaveMessage = 'Guardado automático: ' . now()->format('H:i:s');
    }

    public function finalizar()
    {
        $this->validate([
            'campoFormativoId' => 'required|exists:campo_formativos,id',
            'alumnoId' => 'required|exists:alumnos,id',
            'calificaciones.*' => 'required|numeric|min:1|max:100',
        ]);

        $evaluacion = Evaluacion::find($this->evaluacionId);
        $evaluacion->is_draft = false;
        $evaluacion->save();

        return redirect()->route('evaluaciones.index')
            ->with('success', 'Evaluación finalizada correctamente.');
    }

    public function render()
    {
        return view('livewire.evaluacion.form', [
            'camposFormativos' => CampoFormativo::all(),
            'grupos' => \App\Models\Grupo::all()
        ])->layout('layouts.app');
    }
}
