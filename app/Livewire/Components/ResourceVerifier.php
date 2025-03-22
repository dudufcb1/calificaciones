<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Grupo;
use App\Models\Ciclo;
use App\Models\CampoFormativo;
use App\Models\Momento;
use App\Models\Alumno;
use App\Models\Evaluacion;

class ResourceVerifier extends Component
{
    public $context; // La página actual donde se está mostrando el verificador
    public $showWarning = false;
    public $warningMessage = '';
    public $warningType = 'warning'; // warning, info, error
    public $actionLink = '';
    public $actionText = '';

    // Montaje del componente
    public function mount($context = null)
    {
        $this->context = $context;
        $this->verifyResources();
    }

    // Verifica los recursos según el contexto y establece los mensajes apropiados
    public function verifyResources()
    {
        $userId = auth()->id();

        // Registrar información para depuración
        logger("ResourceVerifier - Contexto: {$this->context}, Usuario: {$userId}");

        switch ($this->context) {
            case 'alumnos':
                $gruposCount = Grupo::where('user_id', $userId)->count();
                if ($gruposCount === 0) {
                    $this->setWarning(
                        'No tienes grupos creados. Los alumnos deben pertenecer a un grupo.',
                        'warning',
                        route('grupos.index'),
                        'Crear un grupo'
                    );
                }
                break;

            case 'momentos':
                $ciclosCount = Ciclo::where('user_id', $userId)->count();
                $camposFormativosCount = CampoFormativo::count(); // Los campos formativos suelen ser globales

                if ($ciclosCount === 0 && $camposFormativosCount === 0) {
                    $this->setWarning(
                        'No tienes ciclos escolares ni campos formativos. Ambos son necesarios para crear momentos educativos.',
                        'error',
                        route('ciclos.index'),
                        'Crear un ciclo escolar'
                    );
                } elseif ($ciclosCount === 0) {
                    $this->setWarning(
                        'No tienes ciclos escolares. Son necesarios para crear momentos educativos.',
                        'warning',
                        route('ciclos.index'),
                        'Crear un ciclo escolar'
                    );
                } elseif ($camposFormativosCount === 0) {
                    $this->setWarning(
                        'No hay campos formativos. Son necesarios para crear momentos educativos.',
                        'warning',
                        route('campos-formativos.index'),
                        'Crear un campo formativo'
                    );
                }
                break;

            case 'evaluaciones':
                $momentosCount = Momento::where('user_id', $userId)->count();
                $gruposCount = Grupo::where('user_id', $userId)->count();
                $alumnosCount = Alumno::where('user_id', $userId)->count();

                if ($momentosCount === 0) {
                    $this->setWarning(
                        'No tienes momentos educativos. Son necesarios para crear evaluaciones.',
                        'error',
                        route('momentos.index'),
                        'Crear un momento educativo'
                    );
                    return;
                } elseif ($gruposCount === 0) {
                    $this->setWarning(
                        'No tienes grupos. Son necesarios para crear evaluaciones.',
                        'warning',
                        route('grupos.index'),
                        'Crear un grupo'
                    );
                } elseif ($alumnosCount === 0) {
                    $this->setWarning(
                        'No tienes alumnos. Son necesarios para realizar evaluaciones.',
                        'warning',
                        route('alumnos.index'),
                        'Crear un alumno'
                    );
                }
                break;

            case 'grupos':
                // No se necesitan recursos previos para crear grupos
                break;

            case 'campos-formativos':
                // No se necesitan recursos previos para crear campos formativos
                break;

            case 'ciclos':
                // No se necesitan recursos previos para crear ciclos
                break;

            case 'dashboard':
                // Verificamos el estado general del sistema
                $this->verifyGeneralResources();
                break;

            default:
                // No hay contexto específico
                break;
        }
    }

    // Verifica el estado general de recursos para el dashboard
    private function verifyGeneralResources()
    {
        $userId = auth()->id();
        $ciclosCount = Ciclo::where('user_id', $userId)->count();
        $gruposCount = Grupo::where('user_id', $userId)->count();
        $alumnosCount = Alumno::where('user_id', $userId)->count();
        $momentosCount = Momento::where('user_id', $userId)->count();
        $camposFormativosCount = CampoFormativo::count();
        $evaluacionesCount = Evaluacion::where('user_id', $userId)->count();

        // Mostrar un mensaje basado en lo que falta configurar en el sistema
        if ($ciclosCount === 0 && $gruposCount === 0) {
            $this->setWarning(
                'Bienvenido al sistema. Para comenzar, crea un ciclo escolar y un grupo.',
                'info',
                route('ciclos.index'),
                'Crear un ciclo escolar'
            );
        } elseif ($gruposCount === 0) {
            $this->setWarning(
                'Para continuar, crea un grupo para poder añadir alumnos.',
                'info',
                route('grupos.index'),
                'Crear un grupo'
            );
        } elseif ($alumnosCount === 0 && $gruposCount > 0) {
            $this->setWarning(
                'Tienes grupos creados. Ahora puedes añadir alumnos a estos grupos.',
                'info',
                route('alumnos.index'),
                'Añadir alumnos'
            );
        } elseif ($camposFormativosCount === 0) {
            $this->setWarning(
                'Para realizar evaluaciones, primero necesitas configurar los campos formativos.',
                'info',
                route('campos-formativos.index'),
                'Configurar campos formativos'
            );
        } elseif ($momentosCount === 0 && $ciclosCount > 0 && $camposFormativosCount > 0) {
            $this->setWarning(
                'Ya tienes ciclos y campos formativos. Ahora puedes crear momentos educativos.',
                'info',
                route('momentos.index'),
                'Crear un momento educativo'
            );
        } elseif ($evaluacionesCount === 0 && $momentosCount > 0 && $alumnosCount > 0) {
            $this->setWarning(
                'Todo está listo para comenzar a realizar evaluaciones.',
                'info',
                route('evaluaciones.index'),
                'Ir a evaluaciones'
            );
        }
    }

    // Establece el mensaje de advertencia y sus propiedades
    private function setWarning($message, $type, $link, $linkText)
    {
        $this->showWarning = true;
        $this->warningMessage = $message;
        $this->warningType = $type;
        $this->actionLink = $link;
        $this->actionText = $linkText;
    }

    public function render()
    {
        return view('livewire.components.resource-verifier');
    }
}
