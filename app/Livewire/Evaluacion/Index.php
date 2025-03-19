<?php

namespace App\Livewire\Evaluacion;

use App\Models\CampoFormativo;
use App\Models\Evaluacion;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $campoFormativoFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'campoFormativoFilter' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCampoFormativoFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Evaluacion::query()
            ->with(['alumno', 'campoFormativo'])
            ->when($this->search, function ($query) {
                $query->whereHas('alumno', function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->campoFormativoFilter, function ($query) {
                $query->where('campo_formativo_id', $this->campoFormativoFilter);
            });

        return view('livewire.evaluacion.index', [
            'evaluaciones' => $query->latest()->paginate(10),
            'camposFormativos' => CampoFormativo::all()
        ])->layout('layouts.app');
    }
}
