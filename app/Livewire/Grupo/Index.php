<?php

namespace App\Livewire\Grupo;

use Livewire\Component;
use App\Models\Grupo;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $grupoId;
    public $showDeleteModal = false;

    public function render()
    {
        $grupos = Grupo::when($this->search, function($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        })->paginate(10);

        return view('livewire.grupo.index', [
            'grupos' => $grupos
        ]);
    }

    public function confirmDelete($id)
    {
        $this->grupoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        Grupo::find($this->grupoId)->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Grupo eliminado correctamente.');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }
}
