<?php

namespace App\Livewire\Evaluacion;

use App\Models\Alumno;
use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\Evaluacion;
use App\Models\EvaluacionDetalle;
use App\Models\Grupo;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public $evaluacionId;
    public $titulo;
    public $descripcion;
    public $fecha_evaluacion;
    public $campoFormativoId;
    public $criterios = [];
    public $grupoId;
    public $alumnosSeleccionados = [];
    public $alumnosEvaluados = [];
    public $editing = false;
    public $autoSaveMessage = '';
    public $mostrarSeleccionAlumnos = false;

    protected $rules = [
        'titulo' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'fecha_evaluacion' => 'nullable|date',
        'campoFormativoId' => 'required|exists:campo_formativos,id',
        'alumnosEvaluados.*.calificaciones.*.valor' => 'required|numeric|min:0|max:100',
    ];

    // Mensajes de validación personalizados
    protected $messages = [
        'alumnosEvaluados.*.calificaciones.*.valor.required' => 'La calificación es obligatoria.',
        'alumnosEvaluados.*.calificaciones.*.valor.numeric' => 'La calificación debe ser un valor numérico.',
        'alumnosEvaluados.*.calificaciones.*.valor.min' => 'La calificación mínima es 0.',
        'alumnosEvaluados.*.calificaciones.*.valor.max' => 'La calificación máxima es 100.',
    ];

    public function mount($evaluacionId = null)
    {
        $this->evaluacionId = $evaluacionId;
        $this->fecha_evaluacion = now()->format('Y-m-d');

        if ($evaluacionId) {
            $this->editing = true;
            $this->loadEvaluacion();
        }
    }

    public function loadEvaluacion()
    {
        $evaluacion = Evaluacion::with('detalles.alumno', 'detalles.criterios')
            ->findOrFail($this->evaluacionId);

        $this->titulo = $evaluacion->titulo;
        $this->descripcion = $evaluacion->descripcion;
        $this->fecha_evaluacion = $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('Y-m-d') : now()->format('Y-m-d');
        $this->campoFormativoId = $evaluacion->campo_formativo_id;

        $this->updatedCampoFormativoId();

        // Cargar los alumnos ya evaluados
        foreach ($evaluacion->detalles as $detalle) {
            $calificaciones = [];

            foreach ($this->criterios as $criterio) {
                $calificacionCriterio = $detalle->criterios->firstWhere('id', $criterio['id']);
                if ($calificacionCriterio) {
                    $calificaciones[] = [
                        'criterio_id' => $criterio['id'],
                        'valor' => $calificacionCriterio->pivot->calificacion,
                        'ponderada' => $calificacionCriterio->pivot->calificacion_ponderada,
                    ];
                } else {
                    $calificaciones[] = [
                        'criterio_id' => $criterio['id'],
                        'valor' => 0,
                        'ponderada' => 0,
                    ];
                }
            }

            $this->alumnosEvaluados[] = [
                'detalle_id' => $detalle->id,
                'alumno_id' => $detalle->alumno_id,
                'nombre' => $detalle->alumno->nombre_completo,
                'calificaciones' => $calificaciones,
                'promedio' => $detalle->promedio_final,
                'observaciones' => $detalle->observaciones
            ];
        }
    }

    public function updatedCampoFormativoId()
    {
        if ($this->campoFormativoId) {
            $this->criterios = Criterio::where('campo_formativo_id', $this->campoFormativoId)
                ->orderBy('orden')
                ->get()
                ->toArray();
        }
    }

    public function updatedGrupoId()
    {
        if ($this->grupoId) {
            $this->alumnosSeleccionados = [];
        }
    }

    public function updated($field)
    {
        // Validación en tiempo real para los campos de calificación
        if (preg_match('/alumnosEvaluados\.\d+\.calificaciones\.\d+\.valor/', $field)) {
            try {
                $this->validateOnly($field);

                // Extraer el índice del alumno del path del índice
                $parts = explode('.', $field);
                if (count($parts) >= 5 && $parts[2] == 'calificaciones' && $parts[4] == 'valor') {
                    $alumnoIndex = (int)$parts[0];
                    $this->calcularPromedio($alumnoIndex);
                }
            } catch (\Exception $e) {
                // Capturamos la excepción para evitar que la página se rompa
                // La validación de errores se mostrará en la vista
            }
        }
    }

    public function toggleSeleccionAlumnos()
    {
        $this->mostrarSeleccionAlumnos = !$this->mostrarSeleccionAlumnos;
    }

    public function cargarAlumnosGrupo()
    {
        if (!$this->grupoId) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Seleccione un grupo primero']);
            return;
        }

        // Obtener los alumnos del grupo seleccionado
        $alumnos = Alumno::where('grupo_id', $this->grupoId)
            ->where('estado', 'activo')
            ->get();

        // Agregar solo los alumnos que no estén ya evaluados
        $alumnosEvaluadosIds = collect($this->alumnosEvaluados)->pluck('alumno_id')->toArray();

        foreach ($alumnos as $alumno) {
            if (!in_array($alumno->id, $alumnosEvaluadosIds)) {
                $this->alumnosSeleccionados[$alumno->id] = true;
            }
        }
    }

    public function agregarAlumnosSeleccionados()
    {
        if (empty($this->alumnosSeleccionados)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'No hay alumnos seleccionados']);
            return;
        }

        if (empty($this->criterios)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Seleccione un campo formativo primero']);
            return;
        }

        // Filtrar solo los IDs de alumnos seleccionados
        $alumnosIds = [];
        foreach ($this->alumnosSeleccionados as $id => $selected) {
            if ($selected) {
                $alumnosIds[] = $id;
            }
        }

        // Obtener los datos de los alumnos
        $alumnos = Alumno::whereIn('id', $alumnosIds)->get();

        // Preparar las calificaciones en blanco para cada criterio
        $calificacionesVacias = [];
        foreach ($this->criterios as $criterio) {
            $calificacionesVacias[] = [
                'criterio_id' => $criterio['id'],
                'valor' => null,
                'ponderada' => 0
            ];
        }

        // Agregar los alumnos a la lista de evaluados
        foreach ($alumnos as $alumno) {
            $this->alumnosEvaluados[] = [
                'alumno_id' => $alumno->id,
                'nombre' => $alumno->nombre_completo,
                'calificaciones' => $calificacionesVacias,
                'promedio' => 0,
                'observaciones' => ''
            ];
        }

        // Limpiar selección
        $this->alumnosSeleccionados = [];
        $this->mostrarSeleccionAlumnos = false;

        $this->autosave();
    }

    public function eliminarAlumno($index)
    {
        $detalle = $this->alumnosEvaluados[$index];

        // Si ya existe un detalle en la BD, eliminarlo
        if (isset($detalle['detalle_id'])) {
            EvaluacionDetalle::find($detalle['detalle_id'])->delete();
        }

        // Eliminar de la lista
        unset($this->alumnosEvaluados[$index]);
        $this->alumnosEvaluados = array_values($this->alumnosEvaluados);

        $this->autosave();
    }

    public function calcularPromedio($alumnoIndex)
    {
        if (empty($this->alumnosEvaluados[$alumnoIndex]['calificaciones']) || empty($this->criterios)) {
            $this->alumnosEvaluados[$alumnoIndex]['promedio'] = 0;
            return;
        }

        $sumaPonderada = 0;
        $sumaPesos = 0;

        foreach ($this->alumnosEvaluados[$alumnoIndex]['calificaciones'] as $index => $calificacion) {
            try {
                $criterioId = $calificacion['criterio_id'];

                // Buscar el criterio correspondiente
                $criterioIndex = collect($this->criterios)->search(function($item) use ($criterioId) {
                    return $item['id'] == $criterioId;
                });

                if ($criterioIndex !== false) {
                    // Asegurarse de que el valor sea numérico antes de calcular
                    $valor = 0;
                    if (isset($calificacion['valor'])) {
                        if (is_numeric($calificacion['valor'])) {
                            $valor = (float) $calificacion['valor'];
                        } else {
                            // Si no es numérico, lo establecemos a 0 y actualizamos el valor
                            $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$index]['valor'] = 0;
                        }
                    }

                    $porcentaje = (float) $this->criterios[$criterioIndex]['porcentaje'];

                    $ponderada = $valor * ($porcentaje / 100);
                    $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$index]['ponderada'] = $ponderada;

                    $sumaPonderada += $ponderada;
                    $sumaPesos += $porcentaje / 100;
                }
            } catch (\Exception $e) {
                // Si hay cualquier error en el cálculo, lo ignoramos y continuamos
                continue;
            }
        }

        $this->alumnosEvaluados[$alumnoIndex]['promedio'] = $sumaPesos > 0 ? round($sumaPonderada / $sumaPesos, 2) : 0;
    }

    public function updatedAlumnosEvaluados($value, $index)
    {
        try {
            // Extraer el índice del alumno del path del índice
            $parts = explode('.', $index);

            if (count($parts) >= 5 && $parts[2] == 'calificaciones' && $parts[4] == 'valor') {
                $alumnoIndex = (int)$parts[0];
                $calIndex = (int)$parts[3];

                // Verificar que el valor sea numérico
                if (isset($this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$calIndex]['valor'])) {
                    $valor = $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$calIndex]['valor'];
                    if (!is_numeric($valor)) {
                        // Si no es numérico, establecerlo a 0
                        $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$calIndex]['valor'] = 0;
                    } else {
                        // Asegurarnos de que esté dentro del rango permitido (0-100)
                        $valor = (float) $valor;
                        if ($valor < 0) $valor = 0;
                        if ($valor > 100) $valor = 100;
                        $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$calIndex]['valor'] = $valor;
                    }
                }

                $this->calcularPromedio($alumnoIndex);
                $this->autosave();
            }
        } catch (\Exception $e) {
            // Capturar cualquier excepción para evitar que la página se rompa
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al actualizar calificación: ' . $e->getMessage()]);
        }
    }

    public function recalcularTodos()
    {
        foreach ($this->alumnosEvaluados as $index => $alumno) {
            $this->calcularPromedio($index);
        }
        $this->autosave();
    }

    public function autosave()
    {
        if (!$this->campoFormativoId || empty($this->titulo)) {
            return;
        }

        // Guardar la evaluación
        $evaluacion = $this->editing
            ? Evaluacion::find($this->evaluacionId)
            : new Evaluacion();

        $evaluacion->titulo = $this->titulo;
        $evaluacion->descripcion = $this->descripcion;
        $evaluacion->fecha_evaluacion = $this->fecha_evaluacion;
        $evaluacion->campo_formativo_id = $this->campoFormativoId;
        $evaluacion->is_draft = true;
        $evaluacion->save();

        if (!$this->editing) {
            $this->evaluacionId = $evaluacion->id;
            $this->editing = true;
        }

        // Guardar los detalles de alumnos
        foreach ($this->alumnosEvaluados as $index => $alumnoData) {
            // Crear o actualizar el detalle
            $detalle = isset($alumnoData['detalle_id'])
                ? EvaluacionDetalle::find($alumnoData['detalle_id'])
                : new EvaluacionDetalle();

            $detalle->evaluacion_id = $evaluacion->id;
            $detalle->alumno_id = $alumnoData['alumno_id'];
            $detalle->promedio_final = $alumnoData['promedio'];
            $detalle->observaciones = $alumnoData['observaciones'] ?? null;
            $detalle->save();

            // Guardar el ID del detalle para futuras actualizaciones
            $this->alumnosEvaluados[$index]['detalle_id'] = $detalle->id;

            // Guardar las calificaciones de los criterios
            foreach ($alumnoData['calificaciones'] as $calificacion) {
                if (isset($calificacion['valor']) && $calificacion['valor'] !== null) {
                    $detalle->criterios()->syncWithoutDetaching([
                        $calificacion['criterio_id'] => [
                            'calificacion' => $calificacion['valor'],
                            'calificacion_ponderada' => $calificacion['ponderada']
                        ]
                    ]);
                }
            }
        }

        $this->autoSaveMessage = 'Guardado automático: ' . now()->format('H:i:s');
    }

    public function finalizar()
    {
        try {
            $this->validate([
                'titulo' => 'required|string|max:255',
                'campoFormativoId' => 'required|exists:campo_formativos,id',
            ]);

            // Verificar que haya al menos un alumno evaluado
            if (empty($this->alumnosEvaluados)) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Debe agregar al menos un alumno a la evaluación']);
                return;
            }

            // Verificar que todas las calificaciones sean valores válidos
            foreach ($this->alumnosEvaluados as $index => $alumno) {
                foreach ($alumno['calificaciones'] as $calIndex => $calificacion) {
                    if (!isset($calificacion['valor']) || !is_numeric($calificacion['valor'])) {
                        $this->dispatch('notify', [
                            'type' => 'error',
                            'message' => "La calificación para el alumno {$alumno['nombre']} debe ser un valor numérico entre 0 y 100"
                        ]);
                        return;
                    }
                }
            }

            // Guardar todo
            $this->autosave();

            // Marcar como finalizada
            $evaluacion = Evaluacion::find($this->evaluacionId);
            $evaluacion->is_draft = false;
            $evaluacion->save();

            return redirect()->route('evaluaciones.index')
                ->with('success', 'Evaluación finalizada correctamente.');
        } catch (\Exception $e) {
            // Capturar cualquier excepción inesperada
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al finalizar la evaluación: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.evaluacion.form', [
            'camposFormativos' => CampoFormativo::all(),
            'grupos' => Grupo::all(),
            'alumnos' => $this->grupoId
                ? Alumno::where('grupo_id', $this->grupoId)->where('estado', 'activo')->get()
                : collect(),
        ]);
    }
}

