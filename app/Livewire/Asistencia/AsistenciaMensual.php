<?php

namespace App\Livewire\Asistencia;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\CampoFormativo;
use App\Models\Ciclo;
use App\Models\DiaConCampoFormativo;
use App\Models\Grupo;
use App\Models\Momento;
use App\Services\AsistenciaService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsistenciaCamposFormativosExport;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class AsistenciaMensual extends Component
{
    public $mes;
    public $anio;
    public $grupo_id;
    public $grupos;
    public $diasDelMes = [];
    public $diasNoLaborables = [];
    public $asistencias = [];
    public $alumnos = [];
    public $estadisticas = [];
    public $editandoNoLaborables = false;

    // Nuevas propiedades para campos formativos
    public $camposFormativos = [];
    public $cicloActual;
    public $momentos = [];
    public $diaSeleccionadoParaCampos = null;
    public $camposFormativosPorDia = [];
    public $camposSeleccionados = [];
    public $editandoCamposFormativos = false;
    public $estadisticasPorCampoFormativo = [];
    public $coloresCamposFormativos = [];

    // Propiedades para los modales adicionales
    public $mostrandoModalRangoFechas = false;
    public $fechaInicioRango = null;
    public $fechaFinRango = null;
    public $selectedDayOfWeek = null;
    public $confirmandoBorrado = false;
    public $confirmandoBorradoFinal = false;

    public function mount()
    {
        // Inicializar con el mes y año actual
        $fecha = Carbon::now();
        $this->mes = $fecha->month;
        $this->anio = $fecha->year;

        // Cargar grupos disponibles
        $this->grupos = Grupo::all();

        // Si hay grupos, seleccionar el primero por defecto
        if ($this->grupos->isNotEmpty()) {
            $this->grupo_id = $this->grupos->first()->id;
        }

        // Cargar campos formativos
        $this->cargarCamposFormativos();

        // Cargar ciclo escolar actual
        $this->cicloActual = Ciclo::where('activo', true)->first();

        // Cargar momentos del ciclo actual
        if ($this->cicloActual) {
            $this->momentos = $this->cicloActual->momentos;
        }

        $this->cargarDiasDelMes();
        $this->cargarAsistencias();
        $this->cargarCamposFormativosPorDia();
        $this->asignarColoresACamposFormativos();
    }

    protected function asignarColoresACamposFormativos()
    {
        $colores = [
            'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500',
            'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500',
            'bg-orange-500', 'bg-cyan-500'
        ];

        $this->coloresCamposFormativos = [];

        foreach ($this->camposFormativos as $index => $campo) {
            $colorIndex = $index % count($colores);
            $this->coloresCamposFormativos[$campo->id] = $colores[$colorIndex];
        }
    }

    public function cargarCamposFormativos()
    {
        $this->camposFormativos = CampoFormativo::all();
    }

    public function cargarCamposFormativosPorDia()
    {
        if (empty($this->grupo_id)) {
            return;
        }

        // Obtener todos los días con campos formativos para este grupo y mes
        $diasConCampos = DiaConCampoFormativo::obtenerPorGrupoYMes($this->grupo_id, $this->anio, $this->mes);

        // Inicializar arreglo de campos formativos por día
        $this->camposFormativosPorDia = [];

        // Agrupar por fecha
        foreach ($diasConCampos as $diaConCampo) {
            $fecha = $diaConCampo->fecha->format('Y-m-d');

            if (!isset($this->camposFormativosPorDia[$fecha])) {
                $this->camposFormativosPorDia[$fecha] = [];
            }

            $this->camposFormativosPorDia[$fecha][] = $diaConCampo->campo_formativo_id;
        }

        $this->calcularEstadisticasPorCampoFormativo();
    }

    public function cargarDiasDelMes()
    {
        $this->diasDelMes = [];
        $fecha = Carbon::createFromDate($this->anio, $this->mes, 1);
        $diasEnMes = $fecha->daysInMonth;

        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $fechaDia = Carbon::createFromDate($this->anio, $this->mes, $dia);
            $this->diasDelMes[] = [
                'numero' => $dia,
                'dia_semana' => $fechaDia->dayOfWeek,
                'es_fin_semana' => $fechaDia->isWeekend(),
                'fecha' => $fechaDia->format('Y-m-d')
            ];
        }

        // Por defecto, marcar fines de semana como no laborables
        $this->diasNoLaborables = collect($this->diasDelMes)
            ->filter(function($dia) {
                return $dia['es_fin_semana'];
            })
            ->pluck('fecha')
            ->toArray();
    }

    public function cambiarMes($incremento)
    {
        $fecha = Carbon::createFromDate($this->anio, $this->mes, 1);
        if ($incremento > 0) {
            $fecha->addMonths($incremento);
        } else {
            $fecha->subMonths(abs($incremento));
        }

        $this->mes = $fecha->month;
        $this->anio = $fecha->year;

        $this->cargarDiasDelMes();
        $this->cargarAsistencias();
        $this->cargarCamposFormativosPorDia();
    }

    public function updatedGrupoId()
    {
        $this->cargarAsistencias();
        $this->cargarCamposFormativosPorDia();
    }

    public function cargarAsistencias()
    {
        if (empty($this->grupo_id)) {
            return;
        }

        // Obtener alumnos del grupo
        $this->alumnos = Alumno::where('grupo_id', $this->grupo_id)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        // Fechas del mes
        $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1)->format('Y-m-d');
        $fechaFin = Carbon::createFromDate($this->anio, $this->mes, count($this->diasDelMes))->format('Y-m-d');

        // Obtener todas las asistencias del mes para este grupo
        $asistenciasDB = Asistencia::whereIn('alumno_id', $this->alumnos->pluck('id'))
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        // Inicializar asistencias vacías para todos los alumnos y días
        $this->asistencias = [];

        foreach ($this->alumnos as $alumno) {
            $this->asistencias[$alumno->id] = [];

            foreach ($this->diasDelMes as $dia) {
                $fecha = $dia['fecha'];

                // Buscar si existe una asistencia para este alumno y fecha
                $asistencia = $asistenciasDB->first(function ($item) use ($alumno, $fecha) {
                    return $item->alumno_id == $alumno->id && $item->fecha->format('Y-m-d') == $fecha;
                });

                $estado = 'falta'; // Por defecto, no hay asistencia

                if ($asistencia) {
                    $estado = $asistencia->estado_normalizado;
                }

                $this->asistencias[$alumno->id][$fecha] = $estado;
            }
        }

        $this->calcularEstadisticas();
        $this->calcularEstadisticasPorCampoFormativo();
    }

    public function guardarAsistencia($alumno_id, $fecha, $estado)
    {
        // Verificar si es un día no laborable
        if (in_array($fecha, $this->diasNoLaborables)) {
            return;
        }

        // Buscar asistencia existente o crear una nueva
        $asistencia = Asistencia::firstOrNew([
            'alumno_id' => $alumno_id,
            'fecha' => $fecha
        ]);

        // Configurar campos según el estado
        switch ($estado) {
            case 'asistio':
                $asistencia->estado = 'asistio';
                $asistencia->asistio = true;
                break;

            case 'falta':
                $asistencia->estado = 'falta';
                $asistencia->asistio = false;
                $asistencia->justificacion = null;
                break;

            case 'justificada':
                $asistencia->estado = 'justificada';
                $asistencia->asistio = false;
                $asistencia->justificacion = 'Justificada desde sistema de asistencia mensual';
                break;
        }

        // Guardar asistencia
        $asistencia->user_id = auth()->id(); // Usuario actual
        $asistencia->save();

        // Actualizar array de asistencias
        $this->asistencias[$alumno_id][$fecha] = $estado;

        // Recalcular estadísticas
        $this->calcularEstadisticas();
        $this->calcularEstadisticasPorCampoFormativo();

        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Asistencia actualizada correctamente'
        ]);
    }

    public function toggleDiaNoLaborable($fecha)
    {
        if (!$this->editandoNoLaborables) {
            return;
        }

        if (in_array($fecha, $this->diasNoLaborables)) {
            $this->diasNoLaborables = array_diff($this->diasNoLaborables, [$fecha]);
        } else {
            $this->diasNoLaborables[] = $fecha;
        }

        $this->calcularEstadisticas();
        $this->calcularEstadisticasPorCampoFormativo();
    }

    public function toggleEdicionNoLaborables()
    {
        $this->editandoNoLaborables = !$this->editandoNoLaborables;
        $this->editandoCamposFormativos = false;
        $this->diaSeleccionadoParaCampos = null;
    }

    public function toggleEdicionCamposFormativos()
    {
        $this->editandoCamposFormativos = !$this->editandoCamposFormativos;
        $this->editandoNoLaborables = false;
        $this->diaSeleccionadoParaCampos = null;
    }

    public function seleccionarDiaParaCampos($fecha)
    {
        if (!$this->editandoCamposFormativos) {
            return;
        }

        // No permitir seleccionar días no laborables
        if (in_array($fecha, $this->diasNoLaborables)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se pueden asignar campos formativos a días no laborables'
            ]);
            return;
        }

        // Si ya está seleccionado, lo deseleccionamos
        if ($this->diaSeleccionadoParaCampos === $fecha) {
            $this->diaSeleccionadoParaCampos = null;
            $this->camposSeleccionados = [];
            return;
        }

        $this->diaSeleccionadoParaCampos = $fecha;

        // Cargamos los campos formativos seleccionados para este día
        $this->camposSeleccionados = $this->camposFormativosPorDia[$fecha] ?? [];
    }

    public function toggleCampoFormativo($campoFormativoId)
    {
        if (!$this->diaSeleccionadoParaCampos) {
            return;
        }

        // Verifica si el campo formativo ya está seleccionado
        $key = array_search($campoFormativoId, $this->camposSeleccionados);

        if ($key !== false) {
            // Si ya existe, lo eliminamos del arreglo
            unset($this->camposSeleccionados[$key]);
            $this->camposSeleccionados = array_values($this->camposSeleccionados);
        } else {
            // Si no existe, lo agregamos
            $this->camposSeleccionados[] = $campoFormativoId;
        }
    }

    public function guardarCamposFormativos()
    {
        if (!$this->diaSeleccionadoParaCampos || !$this->grupo_id) {
            return;
        }

        // Eliminar los campos formativos existentes para este día y grupo
        DiaConCampoFormativo::where('fecha', $this->diaSeleccionadoParaCampos)
            ->where('grupo_id', $this->grupo_id)
            ->delete();

        // Guardar los nuevos campos formativos seleccionados
        foreach ($this->camposSeleccionados as $campoFormativoId) {
            DiaConCampoFormativo::create([
                'fecha' => $this->diaSeleccionadoParaCampos,
                'grupo_id' => $this->grupo_id,
                'campo_formativo_id' => $campoFormativoId
            ]);
        }

        // Actualizar el arreglo local de campos formativos por día
        $this->camposFormativosPorDia[$this->diaSeleccionadoParaCampos] = $this->camposSeleccionados;

        // Recalcular estadísticas
        $this->calcularEstadisticasPorCampoFormativo();

        // Cerrar el modal
        $this->diaSeleccionadoParaCampos = null;
        $this->camposSeleccionados = [];

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Campos formativos guardados correctamente'
        ]);
    }

    /**
     * Aplica la configuración de campos formativos a todos los días similares del mes
     */
    public function aplicarATodosDiasSimilares($fecha)
    {
        if (empty($this->camposSeleccionados) || !$this->grupo_id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar al menos un campo formativo'
            ]);
            return;
        }

        // Obtener el día de la semana de la fecha seleccionada (0-6)
        $diaSemana = date('w', strtotime($fecha));

        // Recorrer todos los días del mes actual
        $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1);
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $diasActualizados = 0;

        for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
            // Si es el mismo día de la semana y no es un día no laborable
            if ($fecha->dayOfWeek == $diaSemana && !in_array($fecha->format('Y-m-d'), $this->diasNoLaborables)) {
                $fechaStr = $fecha->format('Y-m-d');

                // Eliminar campos formativos existentes para este día
                DiaConCampoFormativo::where('fecha', $fechaStr)
                    ->where('grupo_id', $this->grupo_id)
                    ->delete();

                // Guardar los nuevos campos formativos
                foreach ($this->camposSeleccionados as $campoFormativoId) {
                    DiaConCampoFormativo::create([
                        'fecha' => $fechaStr,
                        'grupo_id' => $this->grupo_id,
                        'campo_formativo_id' => $campoFormativoId
                    ]);
                }

                // Actualizar el arreglo local
                $this->camposFormativosPorDia[$fechaStr] = $this->camposSeleccionados;

                $diasActualizados++;
            }
        }

        // Recalcular estadísticas
        $this->calcularEstadisticasPorCampoFormativo();

        // Cerrar el modal
        $this->diaSeleccionadoParaCampos = null;
        $this->camposSeleccionados = [];

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Configuración aplicada a $diasActualizados días similares"
        ]);
    }

    /**
     * Muestra el modal para seleccionar un rango de fechas
     */
    public function mostrarModalRangoFechas()
    {
        if (empty($this->camposSeleccionados)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar al menos un campo formativo'
            ]);
            return;
        }

        // Inicializar fechas con el inicio y fin del mes actual
        $this->fechaInicioRango = Carbon::createFromDate($this->anio, $this->mes, 1)->format('Y-m-d');
        $this->fechaFinRango = Carbon::createFromDate($this->anio, $this->mes, 1)->endOfMonth()->format('Y-m-d');

        // Si viene de un día seleccionado, usamos ese día de la semana por defecto
        if ($this->diaSeleccionadoParaCampos) {
            $this->selectedDayOfWeek = Carbon::parse($this->diaSeleccionadoParaCampos)->dayOfWeek;
        } else {
            $this->selectedDayOfWeek = null;
        }

        $this->mostrandoModalRangoFechas = true;
    }

    /**
     * Cierra el modal de rango de fechas
     */
    public function cerrarModalRangoFechas()
    {
        $this->mostrandoModalRangoFechas = false;
        $this->fechaInicioRango = null;
        $this->fechaFinRango = null;
        $this->selectedDayOfWeek = null;
    }

    /**
     * Aplica la configuración de campos formativos a un rango de fechas
     */
    public function aplicarARangoFechas()
    {
        if (empty($this->camposSeleccionados) || !$this->grupo_id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar al menos un campo formativo'
            ]);
            return;
        }

        if (!$this->fechaInicioRango || !$this->fechaFinRango) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar un rango de fechas válido'
            ]);
            return;
        }

        if ($this->selectedDayOfWeek === null) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar un día de la semana'
            ]);
            return;
        }

        // Validar que la fecha de inicio sea menor o igual que la fecha de fin
        $fechaInicio = Carbon::parse($this->fechaInicioRango);
        $fechaFin = Carbon::parse($this->fechaFinRango);

        if ($fechaInicio->gt($fechaFin)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'La fecha de inicio debe ser menor o igual a la fecha de fin'
            ]);
            return;
        }

        $diasActualizados = 0;

        for ($fecha = $fechaInicio; $fecha->lte($fechaFin); $fecha->addDay()) {
            // Solo procesar los días que coinciden con el día de la semana seleccionado
            // y que no sean días no laborables
            if ($fecha->dayOfWeek == $this->selectedDayOfWeek && !in_array($fecha->format('Y-m-d'), $this->diasNoLaborables)) {
                $fechaStr = $fecha->format('Y-m-d');

                // Eliminar campos formativos existentes para este día
                DiaConCampoFormativo::where('fecha', $fechaStr)
                    ->where('grupo_id', $this->grupo_id)
                    ->delete();

                // Guardar los nuevos campos formativos
                foreach ($this->camposSeleccionados as $campoFormativoId) {
                    DiaConCampoFormativo::create([
                        'fecha' => $fechaStr,
                        'grupo_id' => $this->grupo_id,
                        'campo_formativo_id' => $campoFormativoId
                    ]);
                }

                // Actualizar el arreglo local
                $this->camposFormativosPorDia[$fechaStr] = $this->camposSeleccionados;

                $diasActualizados++;
            }
        }

        // Recalcular estadísticas
        $this->calcularEstadisticasPorCampoFormativo();

        // Cerrar el modal
        $this->mostrandoModalRangoFechas = false;
        $this->fechaInicioRango = null;
        $this->fechaFinRango = null;
        $this->selectedDayOfWeek = null;
        $this->diaSeleccionadoParaCampos = null;
        $this->camposSeleccionados = [];

        $nombresDiasSemana = [
            0 => 'domingos',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miércoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábados',
        ];

        $diaSemanaTexto = $nombresDiasSemana[$this->selectedDayOfWeek] ?? 'días';

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Configuración aplicada a $diasActualizados $diaSemanaTexto en el rango seleccionado"
        ]);
    }

    /**
     * Muestra el primer modal de confirmación para borrar toda la planificación
     */
    public function confirmarBorrarPlanificacion()
    {
        $this->confirmandoBorrado = true;
    }

    /**
     * Cierra el primer modal de confirmación
     */
    public function cerrarModalConfirmacionBorrado()
    {
        $this->confirmandoBorrado = false;
    }

    /**
     * Muestra el segundo modal de confirmación para borrar toda la planificación
     */
    public function confirmarBorradoFinal()
    {
        $this->confirmandoBorrado = false;
        $this->confirmandoBorradoFinal = true;
    }

    /**
     * Cierra el segundo modal de confirmación
     */
    public function cerrarModalConfirmacionBorradoFinal()
    {
        $this->confirmandoBorradoFinal = false;
    }

    /**
     * Borra toda la planificación de campos formativos del mes actual
     */
    public function borrarTodaPlanificacion()
    {
        if (!$this->grupo_id) {
            return;
        }

        // Obtener el primer y último día del mes actual
        $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1)->format('Y-m-d');
        $fechaFin = Carbon::createFromDate($this->anio, $this->mes, 1)->endOfMonth()->format('Y-m-d');

        // Eliminar todos los registros de campos formativos para este grupo y mes
        $registrosEliminados = DiaConCampoFormativo::where('grupo_id', $this->grupo_id)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->delete();

        // Limpiar los arreglos locales
        $this->camposFormativosPorDia = [];
        $this->calcularEstadisticasPorCampoFormativo();

        // Cerrar el modal
        $this->confirmandoBorradoFinal = false;
        $this->diaSeleccionadoParaCampos = null;
        $this->camposSeleccionados = [];

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Se ha eliminado toda la planificación del mes de {$this->nombreMes}"
        ]);
    }

    public function calcularEstadisticasPorCampoFormativo()
    {
        if (empty($this->grupo_id) || empty($this->alumnos)) {
            return;
        }

        // Usar el nuevo servicio para calcular las estadísticas por campo formativo
        $asistenciaService = new AsistenciaService();

        $alumnoIds = $this->alumnos->pluck('id')->toArray();

        $this->estadisticasPorCampoFormativo = $asistenciaService->obtenerEstadisticasPorCampoFormativoMensuales(
            $alumnoIds,
            $this->mes,
            $this->anio,
            $this->diasNoLaborables,
            $this->camposFormativosPorDia
        );
    }

    public function calcularEstadisticas()
    {
        $asistenciaService = new AsistenciaService();

        if (empty($this->grupo_id) || empty($this->alumnos)) {
            $this->estadisticas = [];
            return;
        }

        $alumnoIds = $this->alumnos->pluck('id')->toArray();

        $resultados = $asistenciaService->obtenerEstadisticasMensuales(
            $alumnoIds,
            $this->mes,
            $this->anio,
            $this->diasNoLaborables
        );

        // Formatear resultados para la estructura esperada
        $this->estadisticas = [];
        foreach ($resultados as $alumnoId => $datos) {
            $this->estadisticas[$alumnoId] = [
                'total_dias' => $datos['total_dias'],
                'asistencias' => $datos['asistencias'],
                'inasistencias' => $datos['inasistencias'],
                'justificadas' => $datos['justificadas'],
                'porcentaje_asistencia' => $datos['porcentaje_asistencia'],
                'porcentaje_inasistencia' => $datos['porcentaje_inasistencia'],
            ];
        }
    }

    public function getNombreMesProperty()
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$this->mes];
    }

    public function marcarTodosPresentes($fecha)
    {
        // Verificar si es un día no laborable
        if (in_array($fecha, $this->diasNoLaborables)) {
            return;
        }

        // Iterar por cada alumno y guardar asistencia
        foreach ($this->alumnos as $alumno) {
            $asistencia = Asistencia::firstOrNew([
                'alumno_id' => $alumno->id,
                'fecha' => $fecha
            ]);

            $asistencia->estado = 'asistio';
            $asistencia->asistio = true;
            $asistencia->justificacion = null;
            $asistencia->user_id = auth()->id();
            $asistencia->save();

            // Actualizar array de asistencias
            $this->asistencias[$alumno->id][$fecha] = 'asistio';
        }

        // Recalcular estadísticas
        $this->calcularEstadisticas();
        $this->calcularEstadisticasPorCampoFormativo();

        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Todos los alumnos marcados como presentes'
        ]);
    }

    public function marcarTodosPresentesMes()
    {
        if (empty($this->alumnos)) {
            return;
        }

        // Obtener todos los días laborables del mes
        $diasLaborables = [];
        foreach ($this->diasDelMes as $dia) {
            $fecha = $dia['fecha'];
            if (!in_array($fecha, $this->diasNoLaborables)) {
                $diasLaborables[] = $fecha;
            }
        }

        // Contadores para el mensaje
        $alumnosCount = count($this->alumnos);
        $diasCount = count($diasLaborables);
        $totalRegistros = $alumnosCount * $diasCount;

        // Iterar por cada alumno y cada día laborable
        foreach ($this->alumnos as $alumno) {
            foreach ($diasLaborables as $fecha) {
                $asistencia = Asistencia::firstOrNew([
                    'alumno_id' => $alumno->id,
                    'fecha' => $fecha
                ]);

                $asistencia->estado = 'asistio';
                $asistencia->asistio = true;
                $asistencia->justificacion = null;
                $asistencia->user_id = auth()->id();
                $asistencia->save();

                // Actualizar array de asistencias
                $this->asistencias[$alumno->id][$fecha] = 'asistio';
            }
        }

        // Recalcular estadísticas
        $this->calcularEstadisticas();
        $this->calcularEstadisticasPorCampoFormativo();

        // Notificar al usuario
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Se han marcado {$totalRegistros} asistencias para {$alumnosCount} alumnos en {$diasCount} días laborables"
        ]);
    }

    public function exportarExcel()
    {
        // Verificar que hay datos para exportar
        if (empty($this->grupo_id) || empty($this->alumnos)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No hay datos para exportar'
            ]);
            return;
        }

        $nombreArchivo = 'asistencia_campos_formativos_' . $this->nombreMes . '_' . $this->anio . '.xlsx';

        return Excel::download(
            new AsistenciaCamposFormativosExport(
                $this->alumnos,
                $this->diasDelMes,
                $this->diasNoLaborables,
                $this->asistencias,
                $this->estadisticas,
                $this->camposFormativos,
                $this->camposFormativosPorDia,
                $this->estadisticasPorCampoFormativo,
                $this->nombreMes,
                $this->anio,
                $this->grupos->find($this->grupo_id)->nombre ?? '',
                $this->cicloActual ? $this->cicloActual->nombre_formateado : ''
            ),
            $nombreArchivo
        );
    }

    public function render()
    {
        return view('livewire.asistencia.asistencia-mensual');
    }
}
