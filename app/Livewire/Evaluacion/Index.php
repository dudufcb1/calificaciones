<?php

namespace App\Livewire\Evaluacion;

use App\Models\CampoFormativo;
use App\Models\Evaluacion;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $campoFormativoFilter = '';
    public $evaluacionId;
    public $showDeleteModal = false;

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

    public function confirmDelete($id)
    {
        $this->evaluacionId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->reset(['evaluacionId', 'showDeleteModal']);
    }

    public function deleteEvaluacion()
    {
        $evaluacion = Evaluacion::findOrFail($this->evaluacionId);
        $evaluacion->delete();

        $this->reset(['evaluacionId', 'showDeleteModal']);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Evaluación eliminada correctamente']);
    }

    public function render()
    {
        $query = Evaluacion::query()
            ->with(['detalles.alumno', 'campoFormativo'])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('titulo', 'like', '%' . $this->search . '%')
                      ->orWhereHas('detalles.alumno', function ($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                            ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->campoFormativoFilter, function ($query) {
                $query->where('campo_formativo_id', $this->campoFormativoFilter);
            });

        return view('livewire.evaluacion.index', [
            'evaluaciones' => $query->latest()->paginate(10),
            'camposFormativos' => CampoFormativo::all()
        ]);
    }
}
