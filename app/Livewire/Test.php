<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\WithResourceVerification;

#[Layout('layouts.app')]
class Test extends Component
{
    use WithResourceVerification;

    public function render()
    {
        return view('livewire.test', [
            'resourceContext' => 'evaluaciones' // Forzar el contexto a evaluaciones
        ]);
    }
}
