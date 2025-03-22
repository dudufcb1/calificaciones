<?php

namespace App\Livewire\Grupo;

use Livewire\Component;
use App\Models\Grupo;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Traits\WithResourceVerification;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithResourceVerification;

    public $search = '';
    public $grupoId;
    public $showDeleteModal = false;

    public function render()
    {
        $grupos = Grupo::where('user_id', auth()->id())
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })->paginate(10);

        return view('livewire.grupo.index', [
            'grupos' => $grupos,
            'resourceContext' => $this->getResourceContext()
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
