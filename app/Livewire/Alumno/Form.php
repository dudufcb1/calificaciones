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
    public $editing = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'apellido_materno' => 'required|string|max:255',
        'grupo_id' => 'nullable|exists:grupos,id',
        'estado' => 'required|in:activo,inactivo'
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
            ]);
            session()->flash('message', 'Alumno actualizado correctamente.');
        } else {
            Alumno::create([
                'nombre' => $this->nombre,
                'apellido_paterno' => $this->apellido_paterno,
                'apellido_materno' => $this->apellido_materno,
                'grupo_id' => $this->grupo_id,
                'estado' => $this->estado,
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
