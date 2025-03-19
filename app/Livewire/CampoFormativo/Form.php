<?php

namespace App\Livewire\CampoFormativo;

use Livewire\Component;
use App\Models\CampoFormativo;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public $campoFormativoId;
    public $nombre;
    public $descripcion;
    public $criterios = [];
    public $editing = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'criterios' => 'array',
        'criterios.*.nombre' => 'required|string|max:255',
        'criterios.*.porcentaje' => 'required|numeric|min:0|max:100',
        'criterios.*.descripcion' => 'nullable|string',
    ];

    public function mount($campoFormativoId = null)
    {
        if ($campoFormativoId) {
            $this->editing = true;
            $this->campoFormativoId = $campoFormativoId;
            $campoFormativo = CampoFormativo::with('criterios')->find($campoFormativoId);
            $this->nombre = $campoFormativo->nombre;
            $this->descripcion = $campoFormativo->descripcion;
            $this->criterios = $campoFormativo->criterios->toArray();
        } else {
            $this->addCriterio();
        }
    }

    public function addCriterio()
    {
        $this->criterios[] = [
            'nombre' => '',
            'porcentaje' => 0,
            'descripcion' => ''
        ];
    }

    public function removeCriterio($index)
    {
        unset($this->criterios[$index]);
        $this->criterios = array_values($this->criterios);
    }

    public function save()
    {
        $this->validate();

        // Validar que la suma de porcentajes sea 100%
        $sumaPorcentajes = array_sum(array_column($this->criterios, 'porcentaje'));
        if ($sumaPorcentajes != 100) {
            $this->addError('criterios', 'La suma de los porcentajes debe ser exactamente 100%');
            return;
        }

        if ($this->editing) {
            $campoFormativo = CampoFormativo::find($this->campoFormativoId);
            $campoFormativo->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);

            // Eliminar criterios existentes y crear nuevos
            $campoFormativo->criterios()->delete();
        } else {
            $campoFormativo = CampoFormativo::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
        }

        foreach ($this->criterios as $criterio) {
            $campoFormativo->criterios()->create($criterio);
        }

        session()->flash('message',
            $this->editing ? 'Campo formativo actualizado correctamente.' : 'Campo formativo creado correctamente.'
        );

        return redirect()->route('campos-formativos.index');
    }

    public function render()
    {
        return view('livewire.campo-formativo.form');
    }
}
