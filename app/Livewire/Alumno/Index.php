<?php

namespace App\Livewire\Alumno;

use Livewire\Component;
use App\Models\Alumno;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $alumnoId;
    public $showDeleteModal = false;

    public function render()
    {
        $alumnos = Alumno::when($this->search, function($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        })->paginate(10);

        return view('livewire.alumno.index', [
            'alumnos' => $alumnos
        ]);
    }

    public function confirmDelete($id)
    {
        $this->alumnoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        Alumno::find($this->alumnoId)->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Alumno eliminado correctamente.');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
    }
}
