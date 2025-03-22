<?php

namespace App\Livewire\Ciclos;

use App\Models\Ciclo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Traits\WithResourceVerification;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithResourceVerification;

    public $ciclo_id;
    public $nombre;
    public $anio_inicio;
    public $anio_fin;
    public $activo = false;
    public $isOpen = false;
    public $isConfirmingDelete = false;
    public $search = '';

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

    public function resetInputFields()
    {
        $this->ciclo_id = null;
        $this->nombre = '';
        $this->anio_inicio = date('Y');
        $this->anio_fin = date('Y') + 1;
        $this->activo = false;
    }

    public function store()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'anio_inicio' => 'required|digits:4|integer|min:2000|max:2100',
            'anio_fin' => 'required|digits:4|integer|min:2000|max:2100|gte:anio_inicio',
        ]);

        // Si marcamos este ciclo como activo, desactivamos los demás
        if ($this->activo) {
            Ciclo::where('user_id', auth()->id())->where('activo', true)->update(['activo' => false]);
        }

        Ciclo::updateOrCreate(['id' => $this->ciclo_id], [
            'nombre' => $this->nombre,
            'anio_inicio' => $this->anio_inicio,
            'anio_fin' => $this->anio_fin,
            'activo' => $this->activo,
            'user_id' => auth()->id(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->ciclo_id ? 'Ciclo actualizado correctamente.' : 'Ciclo creado correctamente.'
        ]);

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $ciclo = Ciclo::findOrFail($id);
        $this->ciclo_id = $ciclo->id;
        $this->nombre = $ciclo->nombre;
        $this->anio_inicio = $ciclo->anio_inicio;
        $this->anio_fin = $ciclo->anio_fin;
        $this->activo = $ciclo->activo;

        $this->openModal();
    }

    public function confirmDelete($id)
    {
        $this->ciclo_id = $id;
        $this->isConfirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->isConfirmingDelete = false;
        $this->ciclo_id = null;
    }

    public function delete()
    {
        $ciclo = Ciclo::findOrFail($this->ciclo_id);

        // Verificar si tiene momentos asociados
        if ($ciclo->momentos()->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar el ciclo porque tiene momentos asociados.'
            ]);
            $this->isConfirmingDelete = false;
            return;
        }

        $ciclo->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Ciclo eliminado correctamente.'
        ]);

        $this->isConfirmingDelete = false;
        $this->resetInputFields();
    }

    public function render()
    {
        $ciclos = Ciclo::where('user_id', auth()->id())
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('anio_inicio', 'like', '%' . $this->search . '%')
                    ->orWhere('anio_fin', 'like', '%' . $this->search . '%');
            })
            ->orderBy('anio_inicio', 'desc')
            ->paginate(10);

        return view('livewire.ciclos.index', [
            'ciclos' => $ciclos,
            'resourceContext' => $this->getResourceContext()
        ]);
    }
}
