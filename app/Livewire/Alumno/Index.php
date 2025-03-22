<?php

namespace App\Livewire\Alumno;

use Livewire\Component;
use App\Models\Alumno;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Traits\WithResourceVerification;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithResourceVerification;

    public $search = '';
    public $alumnoId;
    public $showDeleteModal = false;

    public function render()
    {
        // Verificación de depuración
        $userId = auth()->id();
        $resourceContext = 'alumnos'; // Forzar el contexto explícitamente
        $context = $this->getResourceContext();
        $gruposCount = \App\Models\Grupo::where('user_id', $userId)->count();

        $alumnos = Alumno::where('user_id', auth()->id())
            ->when($this->search, function($query) {
                return $query->where('nombre', 'like', '%' . $this->search . '%')
                             ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                             ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
            })->paginate(10);

        return view('livewire.alumno.index', [
            'alumnos' => $alumnos,
            'resourceContext' => $resourceContext
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
