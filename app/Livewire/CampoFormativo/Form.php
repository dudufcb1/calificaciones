<?php

namespace App\Livewire\CampoFormativo;

use Livewire\Component;
use App\Models\CampoFormativo;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public $campoFormativoId;
    public $nombre;
    public $descripcion;
    public $criterios = [];
    public $editing = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'criterios' => 'array',
        'criterios.*.nombre' => 'required|string|max:255',
        'criterios.*.porcentaje' => 'required|numeric|min:0|max:100',
        'criterios.*.descripcion' => 'nullable|string',
    ];

    // Agregamos mensajes de validación personalizados
    protected $messages = [
        'criterios.*.porcentaje.required' => 'El porcentaje es obligatorio.',
        'criterios.*.porcentaje.numeric' => 'El porcentaje debe ser un valor numérico.',
        'criterios.*.porcentaje.min' => 'El porcentaje no puede ser negativo.',
        'criterios.*.porcentaje.max' => 'El porcentaje no puede ser mayor a 100%.',
        'criterios.*.nombre.required' => 'El nombre del criterio es obligatorio.',
    ];

    public function mount($campoFormativoId = null)
    {
        if ($campoFormativoId) {
            $this->editing = true;
            $this->campoFormativoId = $campoFormativoId;
            $campoFormativo = CampoFormativo::with('criterios')->find($campoFormativoId);
            $this->nombre = $campoFormativo->nombre;
            $this->descripcion = $campoFormativo->descripcion;
            $this->criterios = $campoFormativo->criterios->toArray();
        } else {
            $this->addCriterio();
        }
    }

    public function addCriterio()
    {
        $this->criterios[] = [
            'nombre' => '',
            'porcentaje' => 0,
            'descripcion' => ''
        ];
    }

    public function removeCriterio($index)
    {
        unset($this->criterios[$index]);
        $this->criterios = array_values($this->criterios);
    }

    public function updated($field)
    {
        // Se ha desactivado la validación en tiempo real para los campos de porcentaje
        // para evitar llamadas constantes al servidor. La validación se realizará solo al enviar el formulario.
        // Se mantiene la funcionalidad de verificación de suma de porcentajes en el frontend con Alpine.js
    }

    public function save()
    {
        try {
            // Validación antes de procesar
            foreach ($this->criterios as $index => $criterio) {
                // Verificamos que cada porcentaje sea numérico
                if (!isset($criterio['porcentaje']) || !is_numeric($criterio['porcentaje'])) {
                    $this->addError("criterios.{$index}.porcentaje", 'El porcentaje debe ser un valor numérico.');
                    return;
                }

                // Convertimos el porcentaje a número para evitar problemas de tipo
                $this->criterios[$index]['porcentaje'] = (float) $criterio['porcentaje'];
            }

            // Ahora validamos con las reglas definidas
            $this->validate();

            // Verificar la suma de porcentajes
            if (!$this->verificarSumaPorcentajes()) {
                // Si la verificación falla, mostramos un mensaje de error adicional
                session()->flash('error', 'La suma de los porcentajes debe ser exactamente 100%. Ajuste los valores antes de guardar.');
                return;
            }

            if ($this->editing) {
                $campoFormativo = CampoFormativo::find($this->campoFormativoId);
                $campoFormativo->update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                ]);

                // Eliminar criterios existentes y crear nuevos
                $campoFormativo->criterios()->delete();
            } else {
                $campoFormativo = CampoFormativo::create([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                ]);
            }

            foreach ($this->criterios as $criterio) {
                $campoFormativo->criterios()->create([
                    'nombre' => $criterio['nombre'],
                    'porcentaje' => (float) $criterio['porcentaje'],
                    'descripcion' => $criterio['descripcion']
                ]);
            }

            session()->flash('message',
                $this->editing ? 'Campo formativo actualizado correctamente.' : 'Campo formativo creado correctamente.'
            );

            return redirect()->route('campos-formativos.index');
        } catch (\Exception $e) {
            // Capturar cualquier excepción inesperada y mostrar un mensaje de error
            session()->flash('error', 'Ha ocurrido un error: ' . $e->getMessage());
        }
    }

    /**
     * Ajusta automáticamente los porcentajes para que sumen exactamente 100%
     */
    public function ajustarPorcentajes()
    {
        // Verificar que haya al menos un criterio
        if (empty($this->criterios)) {
            session()->flash('error', 'No hay criterios para ajustar. Añada al menos un criterio.');
            return;
        }

        // Calcular la suma actual de porcentajes válidos
        $sumaPorcentajes = 0;
        $criteriosValidos = 0;

        foreach ($this->criterios as $index => $criterio) {
            if (isset($criterio['porcentaje']) && is_numeric($criterio['porcentaje']) && $criterio['porcentaje'] > 0) {
                $sumaPorcentajes += (float) $criterio['porcentaje'];
                $criteriosValidos++;
            } else {
                // Establecer a 0 cualquier valor no numérico
                $this->criterios[$index]['porcentaje'] = 0;
            }
        }

        // Si no hay criterios con valores válidos
        if ($criteriosValidos === 0) {
            // Distribuir el 100% equitativamente entre todos los criterios
            $porcentajePorCriterio = 100 / count($this->criterios);
            foreach ($this->criterios as $index => $criterio) {
                $this->criterios[$index]['porcentaje'] = round($porcentajePorCriterio, 2);
            }
            session()->flash('message', 'Se ha distribuido el 100% equitativamente entre todos los criterios');
            return;
        }

        // Si ya suman exactamente 100%
        if ($sumaPorcentajes == 100) {
            session()->flash('message', 'Los porcentajes ya suman exactamente 100%');
            return;
        }

        // Calcular el factor de ajuste
        $factor = 100 / $sumaPorcentajes;

        // Ajustar cada porcentaje proporcionalmente
        $nuevaSuma = 0;
        foreach ($this->criterios as $index => $criterio) {
            if (isset($criterio['porcentaje']) && is_numeric($criterio['porcentaje']) && $criterio['porcentaje'] > 0) {
                // Ajustar proporcionalmente manteniendo la importancia relativa
                $nuevoValor = round((float) $criterio['porcentaje'] * $factor, 2);
                $this->criterios[$index]['porcentaje'] = $nuevoValor;
                $nuevaSuma += $nuevoValor;
            }
        }

        // Corregir cualquier pequeña diferencia por redondeo
        if ($nuevaSuma != 100) {
            $diferencia = 100 - $nuevaSuma;
            // Añadir la diferencia al último criterio con valor positivo
            foreach (array_reverse(array_keys($this->criterios)) as $index) {
                if ($this->criterios[$index]['porcentaje'] > 0) {
                    $this->criterios[$index]['porcentaje'] += $diferencia;
                    break;
                }
            }
        }

        session()->flash('message', 'Se han ajustado los porcentajes para que sumen exactamente 100%');
    }

    /**
     * Verifica la suma de porcentajes y muestra un error si no es correcta
     */
    protected function verificarSumaPorcentajes()
    {
        // Verificar que todos los criterios tengan porcentajes numéricos
        $sumaPorcentajes = 0;
        foreach ($this->criterios as $criterio) {
            if (isset($criterio['porcentaje']) && is_numeric($criterio['porcentaje'])) {
                $sumaPorcentajes += (float) $criterio['porcentaje'];
            }
        }

        // Limpiar el error anterior si existe
        $this->resetErrorBag('criterios');

        // Mostrar mensaje según la suma
        if ($sumaPorcentajes > 100) {
            $this->addError('criterios', 'La suma actual es ' . $sumaPorcentajes . '%. Debe ser exactamente 100%.');
            return false;
        } else if ($sumaPorcentajes < 100) {
            $this->addError('criterios', 'La suma actual es ' . $sumaPorcentajes . '%. Debe ser exactamente 100%.');
            return false;
        }

        return true;
    }

    public function render()
    {
        return view('livewire.campo-formativo.form');
    }
}
