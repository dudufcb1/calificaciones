<?php

namespace App\Livewire\CampoFormativo;

use Livewire\Component;
use App\Models\CampoFormativo;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $campoFormativoId;
    public $showDeleteModal = false;

    public function render()
    {
        $camposFormativos = CampoFormativo::with('criterios')
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.campo-formativo.index', [
            'camposFormativos' => $camposFormativos
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
}
