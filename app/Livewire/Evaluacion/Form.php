<?php

namespace App\Livewire\Evaluacion;

use App\Enums\MomentoEvaluacion;
use App\Models\Alumno;
use App\Models\CampoFormativo;
use App\Models\Criterio;
use App\Models\Evaluacion;
use App\Models\EvaluacionDetalle;
use App\Models\Grupo;
use App\Models\Momento;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public $evaluacionId;
    public $titulo;
    public $descripcion;
    public $fecha_evaluacion;
    public $momento;
    public $campoFormativoId;
    public $criterios = [];
    public $grupoId;
    public $momentoId;
    public $camposFormativos = [];
    public $alumnosSeleccionados = [];
    public $alumnosEvaluados = [];
    public $editing = false;
    public $autoSaveMessage = '';
    public $mostrarSeleccionAlumnos = false;
    public $selectedCampoFormativo = null;

    protected $rules = [
        'momentoId' => 'required|exists:momentos,id',
        'grupoId' => 'required|exists:grupos,id',
        'alumnosEvaluados.*.calificaciones.*.valor' => 'required|numeric|min:0|max:100',
    ];

    // Mensajes de validación personalizados
    protected $messages = [
        'momentoId.required' => 'Debes seleccionar un momento para evaluar.',
        'grupoId.required' => 'Debes seleccionar un grupo para evaluar.',
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
        $evaluacion = Evaluacion::with(['detalles.alumno', 'detalles.criterios', 'user', 'momentoObj', 'grupo', 'campoFormativo'])
            ->findOrFail($this->evaluacionId);

        $this->titulo = $evaluacion->titulo;
        $this->descripcion = $evaluacion->descripcion;
        $this->fecha_evaluacion = $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('Y-m-d') : now()->format('Y-m-d');

        // Usar el nuevo sistema de momento y grupo
        $this->momentoId = $evaluacion->momento_id;
        $this->grupoId = $evaluacion->grupo_id;
        $this->campoFormativoId = $evaluacion->campo_formativo_id;

        if ($this->momentoId) {
            $this->updatedMomentoId();
        }

        if ($this->campoFormativoId) {
            $this->updatedCampoFormativoId();
        }

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

    public function updatedMomentoId()
    {
        if (!$this->momentoId) {
            $this->camposFormativos = [];
            $this->campoFormativoId = null;
            return;
        }

        // Obtener el momento con sus campos formativos y criterios
        $momento = Momento::with([
            'camposFormativos',
            'camposFormativos.criterios'
        ])->find($this->momentoId);

        if ($momento) {
            $this->camposFormativos = $momento->camposFormativos->toArray();

            // Solo si estamos editando, seleccionar automáticamente un campo formativo
            if ($this->editing) {
                // Si no hay campo formativo seleccionado y hay campos formativos disponibles, seleccionar el primero
                if (!$this->campoFormativoId && count($this->camposFormativos) > 0) {
                    $this->campoFormativoId = $this->camposFormativos[0]['id'];
                    $this->updatedCampoFormativoId();
                }
            } else {
                // En modo creación, limpiamos el campo formativo seleccionado
                // ya que crearemos evaluaciones para todos los campos formativos
                $this->campoFormativoId = null;
                $this->criterios = [];
                $this->alumnosEvaluados = [];
            }
        }
    }

    public function updatedCampoFormativoId()
    {
        if (!$this->campoFormativoId) {
            $this->criterios = [];
            return;
        }

        // Obtener los criterios del campo formativo
        $campoFormativo = CampoFormativo::with(['criterios' => function ($query) {
            $query->orderBy('orden');
        }])->find($this->campoFormativoId);

        if ($campoFormativo) {
            $this->criterios = $campoFormativo->criterios->map(function ($criterio) {
                return [
                    'id' => $criterio->id,
                    'nombre' => $criterio->nombre,
                    'descripcion' => $criterio->descripcion,
                    'porcentaje' => $criterio->porcentaje,
                ];
            })->toArray();
        }
    }

    public function updatedGrupoId()
    {
        if ($this->grupoId) {
            $this->cargarAlumnosGrupo();
        }
    }

    public function updated($field)
    {
        if (str_starts_with($field, 'alumnosEvaluados.')) {
            // Extraer el índice del alumno del nombre del campo
            preg_match('/alumnosEvaluados\.(\d+)/', $field, $matches);
            if (isset($matches[1])) {
                $alumnoIndex = $matches[1];
                $this->calcularPromedio($alumnoIndex);
            }
        }

        // Autosave para formularios editados
        if ($this->editing && $this->evaluacionId) {
            $this->autosave();
        }
    }

    public function toggleSeleccionAlumnos()
    {
        $this->mostrarSeleccionAlumnos = !$this->mostrarSeleccionAlumnos;
    }

    public function cargarAlumnosGrupo()
    {
        if (!$this->grupoId) {
            return;
        }

        $grupo = Grupo::with('alumnos')->find($this->grupoId);
        if (!$grupo) {
            return;
        }

        if ($this->editing && $this->evaluacionId && empty($this->alumnosEvaluados)) {
            // Si estamos editando y no hay alumnos cargados, cargar todos los alumnos del grupo
            // y añadir calificaciones vacías para cada criterio
            $evaluacion = Evaluacion::with(['detalles.alumno', 'detalles.criterios'])->find($this->evaluacionId);
            if ($evaluacion) {
                $this->loadEvaluacion(); // Recarga la evaluación para obtener los alumnos
                return;
            }
        }

        // Esto es para modo creación o si falló la carga en modo edición
        $this->alumnosSeleccionados = [];
        $alumnos = $grupo->alumnos()->orderBy('apellido_paterno')->get();

        foreach ($alumnos as $alumno) {
            // Verificar si el alumno ya está en alumnosEvaluados
            $yaEvaluado = collect($this->alumnosEvaluados)->pluck('alumno_id')->contains($alumno->id);

            if (!$yaEvaluado) {
                $this->alumnosSeleccionados[] = [
                    'id' => $alumno->id,
                    'nombre' => $alumno->nombre_completo,
                    'selected' => true,
                ];
            }
        }

        // Si no hay alumnos seleccionados, agregar directamente todos los alumnos del grupo
        if (empty($this->alumnosEvaluados) && !empty($this->alumnosSeleccionados)) {
            $this->agregarAlumnosSeleccionados();
        }
    }

    public function agregarAlumnosSeleccionados()
    {
        // Verificar que haya criterios si estamos en modo edición
        if ($this->editing && empty($this->criterios) && $this->campoFormativoId) {
            // Intentar cargar los criterios nuevamente
            $this->updatedCampoFormativoId();

            // Si aún no hay criterios, mostrar error
            if (empty($this->criterios)) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No hay criterios de evaluación disponibles para este campo formativo.'
                ]);
                return;
            }
        } else if (empty($this->criterios) && $this->editing) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Seleccione un campo formativo para cargar los criterios de evaluación.'
            ]);
            return;
        }

        $alumnosSeleccionados = collect($this->alumnosSeleccionados)
            ->filter(function ($alumno) {
                return $alumno['selected'] ?? false;
            });

        foreach ($alumnosSeleccionados as $alumno) {
            // Verificar si el alumno ya está en alumnosEvaluados
            $yaEvaluado = collect($this->alumnosEvaluados)->pluck('alumno_id')->contains($alumno['id']);

            if (!$yaEvaluado) {
                $calificaciones = [];

                foreach ($this->criterios as $criterio) {
                    $calificaciones[] = [
                        'criterio_id' => $criterio['id'],
                        'valor' => 0,
                        'ponderada' => 0,
                    ];
                }

                $this->alumnosEvaluados[] = [
                    'detalle_id' => null,
                    'alumno_id' => $alumno['id'],
                    'nombre' => $alumno['nombre'],
                    'calificaciones' => $calificaciones,
                    'promedio' => 0,
                    'observaciones' => ''
                ];
            }
        }

        $this->mostrarSeleccionAlumnos = false;

        // Limpia la selección
        $this->alumnosSeleccionados = array_map(function ($alumno) {
            $alumno['selected'] = false;
            return $alumno;
        }, $this->alumnosSeleccionados);
    }

    public function eliminarAlumno($index)
    {
        // Solo permitir eliminar alumnos si la evaluación es nueva o está en borrador
        if (!$this->editing || ($this->editing && ($this->is_draft ?? true))) {

            // Si el alumno ya tiene un detalle creado, necesitaremos marcarlo para eliminación
            if (isset($this->alumnosEvaluados[$index]['detalle_id']) && $this->alumnosEvaluados[$index]['detalle_id']) {
                // Aquí podrías marcar el detalle para eliminación si es necesario
            }

            // Eliminar del array
            array_splice($this->alumnosEvaluados, $index, 1);

            // Autosave si estamos editando
            if ($this->editing && $this->evaluacionId) {
                $this->autosave();
            }
        }
    }

    public function calcularPromedio($alumnoIndex)
    {
        if (!isset($this->alumnosEvaluados[$alumnoIndex])) {
            return;
        }

        $alumno = &$this->alumnosEvaluados[$alumnoIndex];
        $totalPonderado = 0;
        $totalPorcentaje = 0;

        foreach ($alumno['calificaciones'] as &$calificacion) {
            $criterioId = $calificacion['criterio_id'];
            $valor = floatval($calificacion['valor']);

            // Encontrar el criterio correspondiente
            $criterio = null;
            foreach ($this->criterios as $c) {
                if ($c['id'] == $criterioId) {
                    $criterio = $c;
                    break;
                }
            }

            if ($criterio) {
                $porcentaje = floatval($criterio['porcentaje']);
                $totalPorcentaje += $porcentaje;

                // Calcular el valor ponderado
                $calificacion['ponderada'] = ($valor * $porcentaje) / 100;
                $totalPonderado += $calificacion['ponderada'];
            }
        }

        // Asignar el promedio final
        if ($totalPorcentaje > 0) {
            // Si el total de porcentajes no es 100%, ajustar el promedio
            if ($totalPorcentaje != 100) {
                $totalPonderado = ($totalPonderado * 100) / $totalPorcentaje;
            }
            $alumno['promedio'] = round($totalPonderado, 2);
        } else {
            $alumno['promedio'] = 0;
        }
    }

    public function limpiarCalificaciones($index)
    {
        if (!isset($this->alumnosEvaluados[$index])) {
            return;
        }

        $alumno = &$this->alumnosEvaluados[$index];

        foreach ($alumno['calificaciones'] as &$calificacion) {
            $calificacion['valor'] = 0;
            $calificacion['ponderada'] = 0;
        }

        $alumno['promedio'] = 0;
        $alumno['observaciones'] = '';

        // Autosave si estamos editando
        if ($this->editing && $this->evaluacionId) {
            $this->autosave();
        }
    }

    public function updatedAlumnosEvaluados($value, $index)
    {
        // Verificar si el campo actualizado es una calificación
        if (preg_match('/alumnosEvaluados\.(\d+)\.calificaciones\.(\d+)\.valor/', $index, $matches)) {
            $alumnoIndex = $matches[1];
            $calificacionIndex = $matches[2];

            // Verificar que el valor sea numérico
            if ($value === '' || $value === null) {
                // Si está vacío, establecer en 0
                $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$calificacionIndex]['valor'] = 0;
            } else if (!is_numeric($value)) {
                // Si no es numérico, intentar convertirlo o establecer en 0
                $cleanedValue = preg_replace('/[^\d]/', '', $value);
                $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$calificacionIndex]['valor'] =
                    empty($cleanedValue) ? 0 : (int)$cleanedValue;
            } else {
                // Asegurarse de que el valor esté dentro del rango permitido
                $numericValue = (int)$value;
                if ($numericValue < 0) $numericValue = 0;
                if ($numericValue > 100) $numericValue = 100;
                $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$calificacionIndex]['valor'] = $numericValue;
            }

            // Recalcular el promedio para este alumno
            $this->calcularPromedio($alumnoIndex);
        }
    }

    public function recalcularTodos()
    {
        foreach (array_keys($this->alumnosEvaluados) as $index) {
            $this->calcularPromedio($index);
        }
    }

    public function autosave()
    {
        $this->validate([
            'momentoId' => 'required|exists:momentos,id',
            'grupoId' => 'required|exists:grupos,id',
            'campoFormativoId' => 'required|exists:campo_formativos,id',
        ]);

        $evaluacion = Evaluacion::findOrFail($this->evaluacionId);

        // Generate a more concise title
        $grupo = Grupo::find($this->grupoId);
        $generatedTitle = "Grupo {$grupo->nombre}";

        // Actualizar los datos básicos de la evaluación
        $evaluacion->update([
            'titulo' => $generatedTitle,
            'campo_formativo_id' => $this->campoFormativoId,
            'momento_id' => $this->momentoId,
            'grupo_id' => $this->grupoId,
            'fecha_evaluacion' => $this->fecha_evaluacion,
        ]);

        // Procesar los detalles de evaluación
        foreach ($this->alumnosEvaluados as $alumnoEvaluado) {
            $detalleId = $alumnoEvaluado['detalle_id'] ?? null;
            $alumnoId = $alumnoEvaluado['alumno_id'];
            $promedio = $alumnoEvaluado['promedio'];
            $observaciones = $alumnoEvaluado['observaciones'] ?? '';

            // Crear o actualizar el detalle
            $detalle = EvaluacionDetalle::updateOrCreate(
                [
                    'id' => $detalleId,
                    'evaluacion_id' => $evaluacion->id,
                    'alumno_id' => $alumnoId,
                ],
                [
                    'promedio_final' => $promedio,
                    'observaciones' => $observaciones,
                ]
            );

            // Sincronizar los criterios y calificaciones
            $criteriosData = [];
            foreach ($alumnoEvaluado['calificaciones'] as $calificacion) {
                // Asegurarse de que el valor sea numérico antes de guardarlo
                $valor = $calificacion['valor'];
                if ($valor === '' || $valor === null) {
                    $valor = 0;
                } else if (!is_numeric($valor)) {
                    $valor = preg_replace('/[^\d]/', '', $valor);
                    $valor = empty($valor) ? 0 : (int)$valor;
                } else {
                    $valor = (int)$valor;
                    if ($valor < 0) $valor = 0;
                    if ($valor > 100) $valor = 100;
                }

                $criteriosData[$calificacion['criterio_id']] = [
                    'calificacion' => $valor,
                    'calificacion_ponderada' => (float)$calificacion['ponderada'],
                ];
            }

            $detalle->criterios()->sync($criteriosData);
        }

        // Eliminar detalles que ya no existen en alumnosEvaluados
        $alumnosIds = collect($this->alumnosEvaluados)->pluck('alumno_id')->toArray();
        $evaluacion->detalles()
            ->whereNotIn('alumno_id', $alumnosIds)
            ->delete();

        $evaluacion->recalcularPromedio();

        $this->autoSaveMessage = 'Guardado automáticamente: ' . now()->format('H:i:s');
    }

    public function finalizar()
    {
        // Validation rules differ based on whether we're editing or creating
        if ($this->editing) {
            $this->validate([
                'momentoId' => 'required|exists:momentos,id',
                'grupoId' => 'required|exists:grupos,id',
                'campoFormativoId' => 'required|exists:campo_formativos,id',
                'alumnosEvaluados.*.calificaciones.*.valor' => 'required|numeric|min:0|max:100',
            ]);
        } else {
            $this->validate([
                'momentoId' => 'required|exists:momentos,id',
                'grupoId' => 'required|exists:grupos,id',
            ]);
        }

        // Generate a concise title
        $grupo = Grupo::find($this->grupoId);
        $generatedTitle = "Grupo {$grupo->nombre}";

        $momento = Momento::with([
            'camposFormativos',
            'camposFormativos.criterios'
        ])->find($this->momentoId);

        if (!$momento || $momento->camposFormativos->isEmpty()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'El momento seleccionado no tiene campos formativos asociados.'
            ]);
            return;
        }

        $allCamposFormativos = $momento->camposFormativos;

        // Si estamos editando, actualizar solo la evaluación actual
        if ($this->editing && $this->evaluacionId) {
            $evaluacion = Evaluacion::findOrFail($this->evaluacionId);

            $evaluacion->update([
                'titulo' => $generatedTitle,
                'campo_formativo_id' => $this->campoFormativoId,
                'momento_id' => $this->momentoId,
                'grupo_id' => $this->grupoId,
                'fecha_evaluacion' => $this->fecha_evaluacion,
                'is_draft' => false,
            ]);

            // Procesar los detalles de evaluación para esta evaluación
            $this->procesarDetalles($evaluacion);
            $evaluacion->recalcularPromedio();
        } else {
            // Cargar todos los alumnos del grupo
            $alumnos = $grupo->alumnos()->orderBy('apellido_paterno')->get();

            if ($alumnos->isEmpty()) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'El grupo seleccionado no tiene alumnos asignados.'
                ]);
            }

            // Verificar si ya existen evaluaciones para este momento y grupo
            $existingEvaluaciones = Evaluacion::where('momento_id', $this->momentoId)
                ->where('grupo_id', $this->grupoId)
                ->get()
                ->keyBy('campo_formativo_id');

            $evaluacionesCreadas = 0;

            // Para cada campo formativo del momento, crear o actualizar una evaluación
            foreach ($allCamposFormativos as $campoFormativo) {
                // Verificar si ya existe una evaluación para este campo formativo
                if (isset($existingEvaluaciones[$campoFormativo->id])) {
                    // Si existe, actualizar
                    $evaluacion = $existingEvaluaciones[$campoFormativo->id];
                    $evaluacion->update([
                        'titulo' => $generatedTitle,
                        'fecha_evaluacion' => $this->fecha_evaluacion,
                        'is_draft' => false,
                    ]);
                } else {
                    // Si no existe, crear
                    $evaluacion = Evaluacion::create([
                        'titulo' => $generatedTitle,
                        'campo_formativo_id' => $campoFormativo->id,
                        'momento_id' => $this->momentoId,
                        'grupo_id' => $this->grupoId,
                        'fecha_evaluacion' => $this->fecha_evaluacion,
                        'is_draft' => false,
                    ]);
                    $evaluacionesCreadas++;
                }

                // Si no hay detalles (estudiantes) en esta evaluación, añadirlos automáticamente
                $detallesCount = $evaluacion->detalles()->count();
                if ($detallesCount === 0 && $alumnos->isNotEmpty()) {
                    // Obtener los criterios de este campo formativo
                    $criterios = $campoFormativo->criterios;

                    // Crear detalles de evaluación para cada alumno
                    foreach ($alumnos as $alumno) {
                        $detalle = EvaluacionDetalle::create([
                            'evaluacion_id' => $evaluacion->id,
                            'alumno_id' => $alumno->id,
                            'promedio_final' => 0,
                            'observaciones' => '',
                        ]);

                        // Preparar datos de criterios (inicialmente con calificación 0)
                        $criteriosData = [];
                        foreach ($criterios as $criterio) {
                            $criteriosData[$criterio->id] = [
                                'calificacion' => 0,
                                'calificacion_ponderada' => 0,
                            ];
                        }

                        // Asociar criterios al detalle
                        $detalle->criterios()->sync($criteriosData);
                    }
                }
            }

            $mensaje = $evaluacionesCreadas > 0
                ? "Se han creado $evaluacionesCreadas evaluaciones para todos los campos formativos del momento seleccionado."
                : "Las evaluaciones ya existían y han sido actualizadas.";

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $mensaje
            ]);
        }

        return redirect()->route('evaluaciones.index');
    }

    protected function procesarDetalles($evaluacion)
    {
        // Procesar los detalles de evaluación
        foreach ($this->alumnosEvaluados as $alumnoEvaluado) {
            $detalleId = $alumnoEvaluado['detalle_id'] ?? null;
            $alumnoId = $alumnoEvaluado['alumno_id'];
            $promedio = $alumnoEvaluado['promedio'];
            $observaciones = $alumnoEvaluado['observaciones'] ?? '';

            // Crear o actualizar el detalle
            $detalle = EvaluacionDetalle::updateOrCreate(
                [
                    'id' => $detalleId,
                    'evaluacion_id' => $evaluacion->id,
                    'alumno_id' => $alumnoId,
                ],
                [
                    'promedio_final' => $promedio,
                    'observaciones' => $observaciones,
                ]
            );

            // Sincronizar los criterios y calificaciones
            $criteriosData = [];
            foreach ($alumnoEvaluado['calificaciones'] as $calificacion) {
                // Asegurarse de que el valor sea numérico antes de guardarlo
                $valor = $calificacion['valor'];
                if ($valor === '' || $valor === null) {
                    $valor = 0;
                } else if (!is_numeric($valor)) {
                    $valor = preg_replace('/[^\d]/', '', $valor);
                    $valor = empty($valor) ? 0 : (int)$valor;
                } else {
                    $valor = (int)$valor;
                    if ($valor < 0) $valor = 0;
                    if ($valor > 100) $valor = 100;
                }

                $criteriosData[$calificacion['criterio_id']] = [
                    'calificacion' => $valor,
                    'calificacion_ponderada' => (float)$calificacion['ponderada'],
                ];
            }

            $detalle->criterios()->sync($criteriosData);
        }
    }

    public function render()
    {
        $grupos = Grupo::orderBy('nombre')->get();
        $momentos = Momento::with('camposFormativos')
                          ->where('fecha', '<=', now())
                          ->orderBy('fecha', 'desc')
                          ->get();

        // Solo cargar criterios si está en modo edición
        $showCriterios = $this->editing && !empty($this->criterios);

        // Siempre mostrar la sección de alumnos en modo edición
        $showAlumnos = $this->editing;

        // Si estamos editando y no hay alumnos cargados pero tenemos grupo y campo formativo, cargar alumnos
        if ($this->editing && empty($this->alumnosEvaluados) && $this->grupoId && $this->campoFormativoId) {
            $this->cargarAlumnosGrupo();
        }

        return view('livewire.evaluacion.form', [
            'grupos' => $grupos,
            'momentos' => $momentos,
            'showCriterios' => $showCriterios,
            'showAlumnos' => $showAlumnos,
        ]);
    }
}

