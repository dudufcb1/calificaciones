<?php

namespace App\Livewire\Momentos;

use App\Models\Ciclo;
use App\Models\Momento;
use App\Models\CampoFormativo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $momento_id;
    public $nombre;
    public $fecha;
    public $fecha_inicio = null;
    public $fecha_fin = null;
    public $ciclo_id;
    public $isOpen = false;
    public $isConfirmingDelete = false;
    public $search = '';
    public $ciclo_filter = '';
    public $rangoFechas = false;
    public $camposFormativos = [];
    public $selectedCamposFormativos = [];
    public $isCamposFormativosOpen = false;

    public function mount()
    {
        // Establecer la fecha actual para nuevos momentos
        $this->fecha = date('Y-m-d');

        // Si hay un ciclo activo, seleccionarlo por defecto
        $cicloActivo = Ciclo::where('user_id', auth()->id())->where('activo', true)->first();
        if ($cicloActivo) {
            $this->ciclo_id = $cicloActivo->id;
        }

        // Cargar todos los campos formativos disponibles
        $this->loadCamposFormativos();
    }

    public function loadCamposFormativos()
    {
        $this->camposFormativos = CampoFormativo::orderBy('nombre')->get()->toArray();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function openCamposFormativosModal()
    {
        $this->isCamposFormativosOpen = true;
    }

    public function closeCamposFormativosModal()
    {
        $this->isCamposFormativosOpen = false;
    }

    public function resetInputFields()
    {
        $this->momento_id = null;
        $this->nombre = '';
        $this->fecha = date('Y-m-d');
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->rangoFechas = false;
        $this->selectedCamposFormativos = [];

        // Mantener el ciclo activo si existe
        $cicloActivo = Ciclo::where('user_id', auth()->id())->where('activo', true)->first();
        if ($cicloActivo) {
            $this->ciclo_id = $cicloActivo->id;
        } else {
            $this->ciclo_id = '';
        }
    }

    public function updatedRangoFechas()
    {
        if (!$this->rangoFechas) {
            $this->fecha_inicio = null;
            $this->fecha_fin = null;
        } else if (!$this->fecha_inicio && !$this->fecha_fin) {
            // Si se activa el rango de fechas y no hay fechas establecidas,
            // colocar fecha actual como fecha de inicio
            $this->fecha_inicio = date('Y-m-d');
            $this->fecha_fin = date('Y-m-d', strtotime('+1 week'));
        }
    }

    public function store()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'ciclo_id' => 'required|exists:ciclos,id',
        ];

        if ($this->rangoFechas) {
            $rules['fecha_inicio'] = 'required|date';
            $rules['fecha_fin'] = 'required|date|after_or_equal:fecha_inicio';
        }

        $this->validate($rules);

        $data = [
            'nombre' => $this->nombre,
            'fecha' => $this->fecha,
            'ciclo_id' => $this->ciclo_id,
            'user_id' => auth()->id(),
        ];

        if ($this->rangoFechas) {
            $data['fecha_inicio'] = $this->fecha_inicio;
            $data['fecha_fin'] = $this->fecha_fin;
        } else {
            $data['fecha_inicio'] = null;
            $data['fecha_fin'] = null;
        }

        $momento = Momento::updateOrCreate(['id' => $this->momento_id], $data);

        // Sincronizar los campos formativos seleccionados
        $momento->camposFormativos()->sync($this->selectedCamposFormativos);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->momento_id ? 'Momento actualizado correctamente.' : 'Momento creado correctamente.'
        ]);

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $momento = Momento::with('camposFormativos')->findOrFail($id);
        $this->momento_id = $momento->id;
        $this->nombre = $momento->nombre;
        $this->fecha = $momento->fecha->format('Y-m-d');
        $this->ciclo_id = $momento->ciclo_id;

        if ($momento->fecha_inicio && $momento->fecha_fin) {
            $this->rangoFechas = true;
            $this->fecha_inicio = $momento->fecha_inicio->format('Y-m-d');
            $this->fecha_fin = $momento->fecha_fin->format('Y-m-d');
        } else {
            $this->rangoFechas = false;
            $this->fecha_inicio = null;
            $this->fecha_fin = null;
        }

        // Cargar los campos formativos seleccionados
        $this->selectedCamposFormativos = $momento->camposFormativos->pluck('id')->toArray();

        $this->openModal();
    }

    public function confirmDelete($id)
    {
        $this->momento_id = $id;
        $this->isConfirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->isConfirmingDelete = false;
        $this->momento_id = null;
    }

    public function delete()
    {
        $momento = Momento::findOrFail($this->momento_id);
        $momento->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Momento eliminado correctamente.'
        ]);

        $this->isConfirmingDelete = false;
        $this->momento_id = null;
    }

    public function toggleCampoFormativo($id)
    {
        if (in_array($id, $this->selectedCamposFormativos)) {
            $this->selectedCamposFormativos = array_diff($this->selectedCamposFormativos, [$id]);
        } else {
            $this->selectedCamposFormativos[] = $id;
        }
    }

    public function render()
    {
        $query = Momento::with(['ciclo', 'camposFormativos'])
            ->where('user_id', auth()->id())
            ->when($this->search, function ($q) {
                return $q->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->when($this->ciclo_filter, function ($q) {
                return $q->where('ciclo_id', $this->ciclo_filter);
            })
            ->orderBy('fecha', 'desc');

        $momentos = $query->paginate(10);
        $ciclos = Ciclo::where('user_id', auth()->id())->orderBy('anio_inicio', 'desc')->get();

        return view('livewire.momentos.index', compact('momentos', 'ciclos'));
    }
}
