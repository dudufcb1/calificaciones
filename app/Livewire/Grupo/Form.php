<?php

namespace App\Livewire\Grupo;

use Livewire\Component;
use App\Models\Grupo;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public $grupoId;
    public $nombre;
    public $descripcion;
    public $editing = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string'
    ];

    public function mount($grupoId = null)
    {
        if ($grupoId) {
            $this->editing = true;
            $this->grupoId = $grupoId;
            $grupo = Grupo::find($grupoId);
            $this->nombre = $grupo->nombre;
            $this->descripcion = $grupo->descripcion;
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $grupo = Grupo::find($this->grupoId);
            $grupo->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion
            ]);
            session()->flash('message', 'Grupo actualizado correctamente.');
        } else {
            Grupo::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion
            ]);
            session()->flash('message', 'Grupo creado correctamente.');
        }

        return redirect()->route('grupos.index');
    }

    public function render()
    {
        return view('livewire.grupo.form');
    }
}
