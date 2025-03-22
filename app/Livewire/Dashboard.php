<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\WithResourceVerification;
use App\Models\Ciclo;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Momento;
use App\Models\Evaluacion;
use App\Models\CampoFormativo;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithResourceVerification;

    public function render()
    {
        $userId = auth()->id();

        // Obtener estadísticas
        $stats = [
            'ciclos' => Ciclo::where('user_id', $userId)->count(),
            'grupos' => Grupo::where('user_id', $userId)->count(),
            'alumnos' => Alumno::where('user_id', $userId)->count(),
            'momentos' => Momento::where('user_id', $userId)->count(),
            'evaluaciones' => Evaluacion::where('user_id', $userId)->count(),
            'camposFormativos' => CampoFormativo::count(),
        ];

        // Obtener el ciclo activo
        $cicloActivo = Ciclo::where('user_id', $userId)->where('activo', true)->first();

        // Obtener los últimos momentos educativos
        $ultimosMomentos = Momento::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Obtener las últimas evaluaciones
        $ultimasEvaluaciones = Evaluacion::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'cicloActivo' => $cicloActivo,
            'ultimosMomentos' => $ultimosMomentos,
            'ultimasEvaluaciones' => $ultimasEvaluaciones,
            'resourceContext' => 'dashboard' // Usar 'dashboard' como contexto fijo
        ]);
    }
}
