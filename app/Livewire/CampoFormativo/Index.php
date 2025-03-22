<?php

namespace App\Livewire\CampoFormativo;

use Livewire\Component;
use App\Models\CampoFormativo;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Traits\WithResourceVerification;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithResourceVerification;

    public $search = '';
    public $campoFormativoId;
    public $showDeleteModal = false;

    public function render()
    {
        $camposFormativos = CampoFormativo::with('criterios')
            ->where('user_id', auth()->id())
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.campo-formativo.index', [
            'camposFormativos' => $camposFormativos,
            'resourceContext' => $this->getResourceContext()
        ]);
    }

    public function confirmDelete($id)
    {
        $this->campoFormativoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        CampoFormativo::find($this->campoFormativoId)->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Campo formativo eliminado correctamente.');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }

    /**
     * Duplica un campo formativo existente y sus criterios.
     */
    public function duplicate($id)
    {
        // Obtener el campo formativo original con sus criterios
        $original = CampoFormativo::with('criterios')->findOrFail($id);

        // Crear una copia del campo formativo
        $copia = $original->replicate();
        $copia->nombre = $original->nombre . ' (Copia)';
        $copia->created_at = now();
        $copia->updated_at = now();
        $copia->save();

        // Duplicar los criterios asociados
        foreach ($original->criterios as $criterio) {
            $criterioCopia = $criterio->replicate();
            $criterioCopia->campo_formativo_id = $copia->id;
            $criterioCopia->created_at = now();
            $criterioCopia->updated_at = now();
            $criterioCopia->save();
        }

        session()->flash('message', 'Campo formativo duplicado correctamente.');
    }
}
