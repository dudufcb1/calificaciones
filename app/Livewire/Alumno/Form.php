<?php

namespace App\Livewire\Alumno;

use Livewire\Component;
use App\Models\Alumno;
use App\Models\Grupo;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public $alumnoId;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $grupo_id;
    public $estado = 'activo';
    public $curp;
    public $fecha_nacimiento;
    public $genero;
    public $tutor_nombre;
    public $telefono_tutor;
    public $tutor_email;
    public $direccion;
    public $telefono_emergencia;
    public $alergias;
    public $observaciones;
    public $editing = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'apellido_materno' => 'required|string|max:255',
        'grupo_id' => 'nullable|exists:grupos,id',
        'estado' => 'required|in:activo,inactivo',
        'curp' => 'nullable|string|max:18',
        'fecha_nacimiento' => 'nullable|date',
        'genero' => 'nullable|in:masculino,femenino,otro',
        'tutor_nombre' => 'nullable|string|max:255',
        'telefono_tutor' => 'nullable|string|max:20',
        'tutor_email' => 'nullable|email|max:255',
        'direccion' => 'nullable|string',
        'telefono_emergencia' => 'nullable|string|max:20',
        'alergias' => 'nullable|string',
        'observaciones' => 'nullable|string'
    ];

    public function mount($alumnoId = null)
    {
        if ($alumnoId) {
            $this->editing = true;
            $this->alumnoId = $alumnoId;
            $alumno = Alumno::find($alumnoId);
            $this->nombre = $alumno->nombre;
            $this->apellido_paterno = $alumno->apellido_paterno;
            $this->apellido_materno = $alumno->apellido_materno;
            $this->grupo_id = $alumno->grupo_id;
            $this->estado = $alumno->estado;
            $this->curp = $alumno->curp;
            $this->fecha_nacimiento = $alumno->fecha_nacimiento;
            $this->genero = $alumno->genero;
            $this->tutor_nombre = $alumno->tutor_nombre;
            $this->telefono_tutor = $alumno->telefono_tutor;
            $this->tutor_email = $alumno->tutor_email;
            $this->direccion = $alumno->direccion;
            $this->telefono_emergencia = $alumno->telefono_emergencia;
            $this->alergias = $alumno->alergias;
            $this->observaciones = $alumno->observaciones;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $alumno = Alumno::find($this->alumnoId);
            $alumno->update([
                'nombre' => $this->nombre,
                'apellido_paterno' => $this->apellido_paterno,
                'apellido_materno' => $this->apellido_materno,
                'grupo_id' => $this->grupo_id,
                'estado' => $this->estado,
                'curp' => $this->curp,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'genero' => $this->genero,
                'tutor_nombre' => $this->tutor_nombre,
                'telefono_tutor' => $this->telefono_tutor,
                'tutor_email' => $this->tutor_email,
                'direccion' => $this->direccion,
                'telefono_emergencia' => $this->telefono_emergencia,
                'alergias' => $this->alergias,
                'observaciones' => $this->observaciones,
            ]);
            session()->flash('message', 'Alumno actualizado correctamente.');
        } else {
            Alumno::create([
                'nombre' => $this->nombre,
                'apellido_paterno' => $this->apellido_paterno,
                'apellido_materno' => $this->apellido_materno,
                'grupo_id' => $this->grupo_id,
                'estado' => $this->estado,
                'curp' => $this->curp,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'genero' => $this->genero,
                'tutor_nombre' => $this->tutor_nombre,
                'telefono_tutor' => $this->telefono_tutor,
                'tutor_email' => $this->tutor_email,
                'direccion' => $this->direccion,
                'telefono_emergencia' => $this->telefono_emergencia,
                'alergias' => $this->alergias,
                'observaciones' => $this->observaciones,
            ]);
            session()->flash('message', 'Alumno creado correctamente.');
        }

        return redirect()->route('alumnos.index');
    }

    public function render()
    {
        return view('livewire.alumno.form', [
            'grupos' => Grupo::all()
        ]);
    }
}
