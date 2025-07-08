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
use App\Services\AsistenciaService;
use Carbon\Carbon;
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
    public $is_draft = true;
    public $autoSaveMessage = '';
    public $mostrarSeleccionAlumnos = false;
    public $selectedCampoFormativo = null;

    // Propiedades para el modal de porcentajes de asistencia
    public $mostrarModalAsistencia = false;
    public $porcentajesAsistencia = [];
    public $criterioSeleccionadoId = null;
    public $inicioMes;
    public $finMes;
    public $columnasConPorcentajes = []; // Almacena IDs de columnas con porcentajes ya aplicados
    public $columnaAsignadaPorcentajes = false; // Indica si la columna seleccionada ya tiene porcentajes
    public $hayColumnaConPorcentajes = false; // Indica si ya hay alguna columna con porcentajes aplicados

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
        $this->is_draft = $evaluacion->is_draft;

        // Usar el nuevo sistema de momento y grupo
        $this->momentoId = $evaluacion->momento_id;
        $this->grupoId = $evaluacion->grupo_id;
        $this->campoFormativoId = $evaluacion->campo_formativo_id;

        // Cargar el campo formativo seleccionado para mostrarlo en el modal
        $this->selectedCampoFormativo = $evaluacion->campoFormativo->toArray();

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

        // Verificar si la evaluación está finalizada y prevenir modificaciones
        if ($this->editing && $this->evaluacionId) {
            $evaluacion = Evaluacion::find($this->evaluacionId);
            if ($evaluacion && !$evaluacion->is_draft) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se pueden modificar los criterios de una evaluación finalizada.'
                ]);
                return;
            }
        }

        // Obtener los criterios del campo formativo
        $campoFormativo = CampoFormativo::with(['criterios' => function ($query) {
            $query->orderBy('orden');
        }])->find($this->campoFormativoId);

        if ($campoFormativo) {
            // Preservar calificaciones existentes si estamos editando
            $calificacionesExistentes = [];
            if ($this->editing && !empty($this->alumnosEvaluados)) {
                foreach ($this->alumnosEvaluados as $alumno) {
                    $calificacionesExistentes[$alumno['alumno_id']] = [];
                    foreach ($alumno['calificaciones'] as $calificacion) {
                        $calificacionesExistentes[$alumno['alumno_id']][$calificacion['criterio_id']] = $calificacion;
                    }
                }
            }

            // Actualizar criterios
            $this->criterios = $campoFormativo->criterios->map(function ($criterio) {
                return [
                    'id' => $criterio->id,
                    'nombre' => $criterio->nombre,
                    'descripcion' => $criterio->descripcion,
                    'porcentaje' => $criterio->porcentaje,
                    'es_asistencia' => $criterio->es_asistencia ?? false,
                ];
            })->toArray();

            // Restaurar y actualizar calificaciones preservando datos existentes
            if ($this->editing && !empty($calificacionesExistentes)) {
                foreach ($this->alumnosEvaluados as $alumnoIndex => $alumno) {
                    $nuevasCalificaciones = [];

                    foreach ($this->criterios as $criterio) {
                        // Si existe calificación previa para este criterio, la preservamos
                        if (isset($calificacionesExistentes[$alumno['alumno_id']][$criterio['id']])) {
                            $nuevasCalificaciones[] = $calificacionesExistentes[$alumno['alumno_id']][$criterio['id']];
                        } else {
                            // Si es un criterio nuevo, inicializar con 0
                            $nuevasCalificaciones[] = [
                                'criterio_id' => $criterio['id'],
                                'valor' => 0,
                                'ponderada' => 0,
                            ];
                        }
                    }

                    $this->alumnosEvaluados[$alumnoIndex]['calificaciones'] = $nuevasCalificaciones;

                    // Recalcular promedio
                    $this->calcularPromedio($alumnoIndex);
                }
            }
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

        // Si estamos editando, actualizar solo la evaluación actual (mantener como borrador)
        if ($this->editing && $this->evaluacionId) {
            $evaluacion = Evaluacion::findOrFail($this->evaluacionId);

            $evaluacion->update([
                'titulo' => $generatedTitle,
                'campo_formativo_id' => $this->campoFormativoId,
                'momento_id' => $this->momentoId,
                'grupo_id' => $this->grupoId,
                'fecha_evaluacion' => $this->fecha_evaluacion,
                // Mantener is_draft como está, no cambiar a false aquí
            ]);

            // Procesar los detalles de evaluación para esta evaluación
            $this->procesarDetalles($evaluacion);
            $evaluacion->recalcularPromedio();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Evaluación guardada correctamente.'
            ]);
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
                    // Si existe, actualizar pero mantener como borrador para permitir edición
                    $evaluacion = $existingEvaluaciones[$campoFormativo->id];
                    $evaluacion->update([
                        'titulo' => $generatedTitle,
                        'fecha_evaluacion' => $this->fecha_evaluacion,
                        'is_draft' => true, // Mantener como borrador para permitir edición
                    ]);
                } else {
                    // Si no existe, crear como borrador para permitir edición posterior
                    $evaluacion = Evaluacion::create([
                        'titulo' => $generatedTitle,
                        'campo_formativo_id' => $campoFormativo->id,
                        'momento_id' => $this->momentoId,
                        'grupo_id' => $this->grupoId,
                        'fecha_evaluacion' => $this->fecha_evaluacion,
                        'is_draft' => true, // Crear como borrador para permitir edición
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

    /**
     * Finaliza definitivamente una evaluación marcándola como no borrador
     */
    public function finalizarDefinitivamente()
    {
        if (!$this->editing || !$this->evaluacionId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede finalizar una evaluación que no existe.'
            ]);
            return;
        }

        // Validar que todas las calificaciones estén completas
        $this->validate([
            'alumnosEvaluados.*.calificaciones.*.valor' => 'required|numeric|min:0|max:100',
        ]);

        $evaluacion = Evaluacion::findOrFail($this->evaluacionId);

        // Marcar como finalizada
        $evaluacion->update(['is_draft' => false]);

        // Procesar los detalles de evaluación
        $this->procesarDetalles($evaluacion);
        $evaluacion->recalcularPromedio();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Evaluación finalizada correctamente. Ya no se podrá editar.'
        ]);

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

    /**
     * Detecta automáticamente el criterio de asistencia
     */
    public function detectarCriterioAsistencia()
    {
        // Primero buscar por el marcador es_asistencia
        foreach ($this->criterios as $criterio) {
            if (isset($criterio['es_asistencia']) && $criterio['es_asistencia']) {
                return $criterio['id'];
            }
        }

        // Si no se encuentra, buscar por regex en el nombre
        $patronesAsistencia = [
            '/^asistencia$/i',
            '/^pase\s+de\s+lista$/i',
            '/^lista$/i',
            '/asistencia/i',
            '/pase.*lista/i',
            '/lista.*asistencia/i'
        ];

        foreach ($this->criterios as $criterio) {
            foreach ($patronesAsistencia as $patron) {
                if (preg_match($patron, trim($criterio['nombre']))) {
                    return $criterio['id'];
                }
            }
        }

        return null;
    }

    /**
     * Muestra el modal para aplicar porcentajes de asistencia
     */
    public function mostrarModalAsistencias()
    {
        // Verificar que estemos en modo edición y que haya alumnos y criterios cargados
        if (!$this->editing || empty($this->alumnosEvaluados) || empty($this->criterios)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se pueden aplicar porcentajes de asistencia en este momento.'
            ]);
            return;
        }

        // Obtener el campo formativo actual
        $campoFormativo = CampoFormativo::find($this->campoFormativoId);
        if (!$campoFormativo) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se encontró el campo formativo seleccionado.'
            ]);
            return;
        }

        // Detectar automáticamente el criterio de asistencia
        $criterioAsistenciaId = $this->detectarCriterioAsistencia();

        if ($criterioAsistenciaId) {
            // Si se detecta automáticamente, aplicar directamente
            $this->criterioSeleccionadoId = $criterioAsistenciaId;
            $this->aplicarPorcentajesAsistenciaDirecto();
            return;
        }

        // Si no se detecta automáticamente, mostrar modal de selección manual
        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => 'No se detectó automáticamente un criterio de asistencia. Seleccione manualmente la columna correspondiente.'
        ]);

        // Guardar el campo formativo seleccionado para mostrarlo en el modal
        $this->selectedCampoFormativo = $campoFormativo->toArray();

        // Definir el mes actual para consultar asistencias (por defecto el último mes)
        $fecha = Carbon::now();
        $this->inicioMes = $fecha->startOfMonth()->format('Y-m-d');
        $this->finMes = $fecha->endOfMonth()->format('Y-m-d');

        // Obtener los porcentajes de asistencia para este campo formativo
        $this->obtenerPorcentajesAsistencia();

        // Analizar qué columnas ya tienen porcentajes de asistencia aplicados
        $this->detectarColumnasConPorcentajes();

        // Restablecer selección
        $this->criterioSeleccionadoId = null;
        $this->columnaAsignadaPorcentajes = false;

        // Verificar si ya hay alguna columna con porcentajes aplicados
        $this->hayColumnaConPorcentajes = count($this->columnasConPorcentajes) > 0;

        // Mostrar el modal
        $this->mostrarModalAsistencia = true;
    }

    /**
     * Detecta qué columnas ya tienen porcentajes de asistencia aplicados
     */
    public function detectarColumnasConPorcentajes()
    {
        // Este es un método simplificado para detectar columnas con porcentajes ya aplicados
        // En una implementación real, podrías guardar esta información en la base de datos

        $this->columnasConPorcentajes = [];

        // Simular la detección analizando si los valores coinciden con los porcentajes de asistencia
        if (!empty($this->alumnosEvaluados) && !empty($this->porcentajesAsistencia)) {
            foreach ($this->criterios as $criterioIndex => $criterio) {
                $coincidencias = 0;
                $totalAlumnos = count($this->alumnosEvaluados);
                $totalConAsistencia = 0;

                foreach ($this->alumnosEvaluados as $alumnoIndex => $alumno) {
                    $alumnoId = $alumno['alumno_id'];

                    // Verificar si este alumno tiene porcentaje de asistencia
                    if (isset($this->porcentajesAsistencia[$alumnoId])) {
                        $porcentajeAsistencia = round($this->porcentajesAsistencia[$alumnoId]['porcentaje']);
                        $calificacionActual = intval($alumno['calificaciones'][$criterioIndex]['valor']);

                        if ($porcentajeAsistencia > 0) {
                            $totalConAsistencia++;

                            // Si la calificación coincide con el porcentaje de asistencia
                            if ($calificacionActual == $porcentajeAsistencia) {
                                $coincidencias++;
                            }
                        }
                    }
                }

                // Si más del 80% de los alumnos con asistencia tienen calificaciones que coinciden con sus porcentajes
                // asumimos que esta columna ya tiene porcentajes aplicados
                if ($totalConAsistencia > 0 && ($coincidencias / $totalConAsistencia) >= 0.8) {
                    $this->columnasConPorcentajes[] = $criterio['id'];
                }
            }
        }
    }

    /**
     * Se ejecuta cuando se cambia la selección de criterio
     */
    public function updatedCriterioSeleccionadoId()
    {
        if ($this->criterioSeleccionadoId) {
            // Verificar si esta columna ya tiene porcentajes asignados
            $this->columnaAsignadaPorcentajes = in_array($this->criterioSeleccionadoId, $this->columnasConPorcentajes);
        } else {
            $this->columnaAsignadaPorcentajes = false;
        }
    }

    /**
     * Resetea la asignación de porcentajes para una columna
     */
    public function resetearAsignacionPorcentajes()
    {
        // Si hay un criterio seleccionado, solo reset ese criterio
        if ($this->criterioSeleccionadoId) {
            // Quitar este criterio de la lista de columnas con porcentajes
            $this->columnasConPorcentajes = array_filter($this->columnasConPorcentajes, function($id) {
                return $id != $this->criterioSeleccionadoId;
            });
        } else {
            // Si no hay un criterio seleccionado pero hay una columna con porcentajes,
            // resetear todas las columnas
            $this->columnasConPorcentajes = [];
        }

        $this->columnaAsignadaPorcentajes = false;
        $this->hayColumnaConPorcentajes = count($this->columnasConPorcentajes) > 0;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'La asignación de porcentajes se ha eliminado. Ahora puede volver a aplicar porcentajes a esta columna.'
        ]);
    }

    /**
     * Aplica los porcentajes de asistencia directamente al criterio detectado automáticamente
     */
    public function aplicarPorcentajesAsistenciaDirecto()
    {
        // Obtener porcentajes de asistencia
        $this->obtenerPorcentajesAsistencia();

        if (empty($this->porcentajesAsistencia)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se encontraron datos de asistencia para aplicar.'
            ]);
            return;
        }

        // Verificar si el criterio ya tiene porcentajes aplicados
        $criterioNombre = '';
        foreach ($this->criterios as $criterio) {
            if ($criterio['id'] == $this->criterioSeleccionadoId) {
                $criterioNombre = $criterio['nombre'];
                break;
            }
        }

        // Aplicar directamente sin confirmación adicional
        $totalActualizados = $this->aplicarPorcentajesInterno();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Porcentajes de asistencia aplicados automáticamente al criterio '{$criterioNombre}'. Se actualizaron {$totalActualizados} alumnos."
        ]);
    }

    /**
     * Aplica los porcentajes de asistencia a la columna seleccionada
     */
    public function aplicarPorcentajesAsistencia()
    {
        // Verificar que se haya seleccionado un criterio
        if (!$this->criterioSeleccionadoId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar una columna para aplicar los porcentajes.'
            ]);
            return;
        }

        // Verificar si el criterio seleccionado parece ser de asistencia
        $criterioSeleccionado = null;
        foreach ($this->criterios as $criterio) {
            if ($criterio['id'] == $this->criterioSeleccionadoId) {
                $criterioSeleccionado = $criterio;
                break;
            }
        }

        if ($criterioSeleccionado && !isset($criterioSeleccionado['es_asistencia'])) {
            // Verificar con regex si parece ser de asistencia
            $patronesAsistencia = [
                '/asistencia/i',
                '/pase.*lista/i',
                '/lista/i'
            ];

            $pareceAsistencia = false;
            foreach ($patronesAsistencia as $patron) {
                if (preg_match($patron, trim($criterioSeleccionado['nombre']))) {
                    $pareceAsistencia = true;
                    break;
                }
            }

            if (!$pareceAsistencia) {
                $this->dispatch('confirm-apply-attendance', [
                    'criterio' => $criterioSeleccionado['nombre'],
                    'message' => "El criterio '{$criterioSeleccionado['nombre']}' no parece estar relacionado con asistencia. ¿Está seguro de aplicar los porcentajes de asistencia a esta columna?"
                ]);
                return;
            }
        }

        // Verificar si ya hay alguna columna con porcentajes y esta no es la misma
        if ($this->hayColumnaConPorcentajes && !in_array($this->criterioSeleccionadoId, $this->columnasConPorcentajes)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Ya existe una columna con porcentajes aplicados. Debe eliminar esa asignación primero.'
            ]);
            return;
        }

        // Verificar si esta columna ya tiene porcentajes asignados
        if ($this->columnaAsignadaPorcentajes) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Esta columna ya tiene porcentajes de asistencia aplicados. Resetee la asignación primero.'
            ]);
            return;
        }

        // Buscar el índice del criterio seleccionado
        $criterioIndex = null;
        foreach ($this->criterios as $index => $criterio) {
            if ($criterio['id'] == $this->criterioSeleccionadoId) {
                $criterioIndex = $index;
                break;
            }
        }

        if ($criterioIndex === null) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se encontró la columna seleccionada.'
            ]);
            return;
        }

        // Aplicar los porcentajes usando el método interno
        $totalActualizados = $this->aplicarPorcentajesInterno();

        // Cerrar el modal después de aplicar
        $this->mostrarModalAsistencia = false;

        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Se aplicaron porcentajes de asistencia a {$totalActualizados} alumnos."
        ]);
    }

    /**
     * Método interno para aplicar porcentajes de asistencia
     */
    private function aplicarPorcentajesInterno()
    {
        // Buscar el índice del criterio seleccionado
        $criterioIndex = null;
        foreach ($this->criterios as $index => $criterio) {
            if ($criterio['id'] == $this->criterioSeleccionadoId) {
                $criterioIndex = $index;
                break;
            }
        }

        if ($criterioIndex === null) {
            return 0;
        }

        // Aplicar los porcentajes de asistencia a la columna seleccionada
        $totalActualizados = 0;

        foreach ($this->porcentajesAsistencia as $alumnoId => $datos) {
            $alumnoIndex = $datos['index'];
            $porcentaje = $datos['porcentaje'];

            // Actualizar la calificación con el porcentaje de asistencia
            $this->alumnosEvaluados[$alumnoIndex]['calificaciones'][$criterioIndex]['valor'] = round($porcentaje);

            $totalActualizados++;
        }

        // Recalcular promedios
        $this->recalcularTodos();

        // Marcar esta columna como asignada
        $this->columnasConPorcentajes[] = $this->criterioSeleccionadoId;
        $this->columnaAsignadaPorcentajes = true;
        $this->hayColumnaConPorcentajes = true;

        // Guardar automáticamente
        $this->autosave();

        return $totalActualizados;
    }

    /**
     * Confirma la aplicación de porcentajes cuando el criterio no parece ser de asistencia
     */
    public function confirmarAplicarAsistencia()
    {
        $totalActualizados = $this->aplicarPorcentajesInterno();

        // Cerrar el modal después de aplicar
        $this->mostrarModalAsistencia = false;

        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Se aplicaron porcentajes de asistencia a {$totalActualizados} alumnos."
        ]);
    }

    /**
     * Obtiene los porcentajes de asistencia de los alumnos para el campo formativo actual
     */
    public function obtenerPorcentajesAsistencia()
    {
        // Obtener IDs de los alumnos
        $alumnoIds = collect($this->alumnosEvaluados)->pluck('alumno_id')->toArray();

        // Asegurar que las fechas estén inicializadas
        if (empty($this->inicioMes) || empty($this->finMes)) {
            $fecha = Carbon::now();
            $this->inicioMes = $fecha->startOfMonth()->format('Y-m-d');
            $this->finMes = $fecha->endOfMonth()->format('Y-m-d');
        }

        // Usar el servicio de asistencia para obtener los porcentajes
        $asistenciaService = new AsistenciaService();

        // Obtener porcentajes para el campo formativo específico
        $estadisticasPorCampo = $asistenciaService->calcularEstadisticasPorCampoFormativo(
            $alumnoIds,
            $this->inicioMes,
            $this->finMes
        );

        // Formatear los resultados para mostrarlos en el modal
        $this->porcentajesAsistencia = [];

        foreach ($this->alumnosEvaluados as $index => $alumno) {
            $alumnoId = $alumno['alumno_id'];

            // Verificar si tenemos estadísticas para este alumno y este campo formativo
            if (isset($estadisticasPorCampo[$alumnoId][$this->campoFormativoId])) {
                $stats = $estadisticasPorCampo[$alumnoId][$this->campoFormativoId];
                $this->porcentajesAsistencia[$alumnoId] = [
                    'nombre' => $alumno['nombre'] ?? $alumno['nombre_completo'] ?? "Alumno #{$alumnoId}",
                    'porcentaje' => $stats['porcentaje_asistencia'],
                    'total_dias' => $stats['total_dias'],
                    'asistencias' => $stats['asistencias'],
                    'inasistencias' => $stats['inasistencias'],
                    'index' => $index // Guardamos el índice para aplicar después
                ];
            } else {
                // Si no hay estadísticas, mostrar un valor predeterminado
                $this->porcentajesAsistencia[$alumnoId] = [
                    'nombre' => $alumno['nombre'] ?? $alumno['nombre_completo'] ?? "Alumno #{$alumnoId}",
                    'porcentaje' => 0,
                    'total_dias' => 0,
                    'asistencias' => 0,
                    'inasistencias' => 0,
                    'index' => $index
                ];
            }
        }
    }

    /**
     * Cierra el modal de porcentajes de asistencia
     */
    public function cerrarModalAsistencia()
    {
        $this->mostrarModalAsistencia = false;
    }

    /**
     * Actualiza el rango de fechas y recalcula los porcentajes
     */
    public function actualizarPorcentajesAsistencia()
    {
        $this->obtenerPorcentajesAsistencia();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Porcentajes de asistencia actualizados correctamente.'
        ]);
    }
}

