<div class="py-6">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Cabecera con selección de grupo y mes -->
                <div class="flex flex-col space-y-4 mb-6">
                    <!-- Primera fila: título y grupo/mes -->
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <div class="flex items-center">
                            <h2 class="text-2xl font-bold">Control de Asistencia Mensual</h2>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select wire:model.live="grupo_id" class="border border-gray-300 rounded px-3 py-2 text-gray-700 focus:outline-none">
                                <option value="">Seleccione grupo</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                @endforeach
                            </select>

                            <!-- Control de mes -->
                            <button wire:click="cambiarMes(-1)" class="px-3 py-2 bg-gray-100 rounded-l hover:bg-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div class="px-4 py-2 bg-gray-100 text-center font-medium" style="min-width: 150px;">
                                {{ $this->nombreMes }} {{ $anio }}
                            </div>
                            <button wire:click="cambiarMes(1)" class="px-3 py-2 bg-gray-100 rounded-r hover:bg-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Segunda fila: botones de acción -->
                    <div class="flex flex-wrap justify-center md:justify-end gap-2">
                        <!-- Botón para editar días no laborables -->
                        <button wire:click="toggleEdicionNoLaborables" class="px-3 py-2 rounded text-white {{ $editandoNoLaborables ? 'bg-red-500 hover:bg-red-600' : 'bg-indigo-500 hover:bg-indigo-600' }}">
                            {{ $editandoNoLaborables ? 'Terminar edición' : 'Editar días no laborables' }}
                        </button>

                        <!-- Botón para editar campos formativos por día -->
                        <button wire:click="toggleEdicionCamposFormativos" class="px-3 py-2 rounded text-white {{ $editandoCamposFormativos ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                            {{ $editandoCamposFormativos ? 'Terminar edición' : 'Elegir campos formativos por día' }}
                        </button>

                        <!-- Botón para exportar Excel -->
                        <button wire:click="exportarExcel" class="px-3 py-2 rounded text-white bg-blue-500 hover:bg-blue-600 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Exportar Excel
                        </button>
                    </div>
                </div>

                @if($grupo_id && count($alumnos) > 0 && !$editandoNoLaborables && !$editandoCamposFormativos)
                <div class="mb-4 flex justify-center">
                    <button
                        wire:click="marcarTodosPresentesMes"
                        wire:confirm="¿Está seguro que desea marcar a TODOS los alumnos como presentes en TODOS los días laborables del mes? Esta acción sobreescribirá cualquier asistencia existente."
                        class="px-4 py-2 bg-green-500 text-white rounded shadow hover:bg-green-600 flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Marcar a todos presentes en todo el mes
                    </button>
                </div>
                @endif

                @if($editandoNoLaborables)
                    <div class="mb-4 p-3 bg-yellow-100 rounded">
                        <p class="text-sm text-yellow-800">
                            <strong>Modo edición:</strong> Haga clic en las fechas de la cabecera para marcar/desmarcar días no laborables.
                            Los días no laborables se mostrarán en gris y no se contarán para el cálculo de porcentajes.
                        </p>
                    </div>
                @endif

                @if($editandoCamposFormativos)
                    <div class="mb-4 p-3 bg-green-100 rounded">
                        <p class="text-sm text-green-800">
                            <strong>Modo edición de campos formativos:</strong> Haga clic en las fechas de la cabecera para seleccionar los campos formativos que se trabajarán ese día.
                            Los días con campos formativos asignados se mostrarán con indicadores de color.
                            <strong>Nota:</strong> No se pueden asignar campos formativos a días no laborables (marcados en gris).
                        </p>
                    </div>
                @endif

                <!-- Leyenda de colores para campos formativos -->
                @if(!empty($camposFormativosPorDia) && !$editandoCamposFormativos && !$editandoNoLaborables)
                    <div class="mb-4 p-3 bg-blue-50 rounded">
                        <h3 class="font-semibold mb-2">Campos formativos asociados al mes:</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($camposFormativos as $campo)
                                <div class="flex items-center">
                                    <div class="w-4 h-4 mr-1 {{ $coloresCamposFormativos[$campo->id] ?? 'bg-gray-300' }}"></div>
                                    <span class="text-sm">{{ $campo->nombre }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!$grupo_id)
                    <div class="text-center p-6">
                        <p class="text-gray-500">Seleccione un grupo para ver la asistencia</p>
                    </div>
                @elseif(count($alumnos) === 0)
                    <div class="text-center p-6">
                        <p class="text-gray-500">Este grupo no tiene alumnos asignados</p>
                    </div>
                @else
                    <!-- Tabla de asistencia -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                            <thead>
                                <!-- Franja de mes y año -->
                                <tr>
                                    <th colspan="4" class="px-6 py-2 bg-yellow-300 text-center font-bold text-lg border-b border-gray-300 sticky left-0 z-10">
                                        Ciclo escolar {{ $cicloActual ? $cicloActual->nombre_formateado : '2023-2024' }}
                                    </th>
                                    <th colspan="{{ count($diasDelMes) }}" class="px-6 py-2 bg-yellow-300 text-center font-bold text-lg border-b border-gray-300">
                                        {{ strtoupper($this->nombreMes) }}
                                    </th>
                                    <th colspan="4" class="px-6 py-2 bg-yellow-300 text-center font-bold text-lg border-b border-gray-300">
                                        Estadísticas
                                    </th>
                                </tr>

                                <!-- Sección de títulos -->
                                <tr class="bg-blue-200">
                                    <th class="px-1 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300 sticky left-0 z-10 bg-blue-200">No.</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-800 border border-gray-300 sticky left-8 z-10 bg-blue-200">Nombre</th>
                                    <th colspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300 sticky left-40 z-10 bg-blue-200">Apellidos</th>

                                    <!-- Días del mes -->
                                    @foreach($diasDelMes as $dia)
                                        <th
                                            wire:click="{{ $editandoNoLaborables ? "toggleDiaNoLaborable('{$dia['fecha']}')" : ($editandoCamposFormativos && !in_array($dia['fecha'], $diasNoLaborables) ? "seleccionarDiaParaCampos('{$dia['fecha']}')" : '') }}"
                                            class="relative px-1 py-1 text-center text-xs font-medium
                                            {{ in_array($dia['fecha'], $diasNoLaborables) ? 'bg-gray-300' : ($dia['es_fin_semana'] ? 'bg-gray-100' : 'bg-blue-200') }}
                                            {{ ($editandoNoLaborables || ($editandoCamposFormativos && !in_array($dia['fecha'], $diasNoLaborables))) ? 'cursor-pointer' : 'cursor-default' }}
                                            border border-gray-300
                                            {{ $editandoNoLaborables ? 'hover:bg-yellow-200' : '' }}
                                            {{ $editandoCamposFormativos && !in_array($dia['fecha'], $diasNoLaborables) ? 'hover:bg-green-200' : '' }}"
                                        >
                                            <div class="flex flex-col items-center">
                                                <span>{{ $dia['numero'] }}</span>

                                                <!-- Indicador de campos formativos -->
                                                @if(isset($camposFormativosPorDia[$dia['fecha']]) && !empty($camposFormativosPorDia[$dia['fecha']]))
                                                    <div class="flex mt-1 justify-center">
                                                        @foreach($camposFormativosPorDia[$dia['fecha']] as $campoFormativoId)
                                                            <div class="w-2 h-2 mx-px rounded-full {{ $coloresCamposFormativos[$campoFormativoId] ?? 'bg-gray-500' }}"></div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if(!in_array($dia['fecha'], $diasNoLaborables) && !$editandoNoLaborables && !$editandoCamposFormativos)
                                                    <button
                                                        wire:click.stop="marcarTodosPresentes('{{ $dia['fecha'] }}')"
                                                        class="mt-1 text-xs bg-green-500 text-white rounded px-1 hover:bg-green-600"
                                                        title="Marcar todos presentes"
                                                    >
                                                        ✓ Todos
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Indicador de día seleccionado para editar campos -->
                                            @if($diaSeleccionadoParaCampos === $dia['fecha'])
                                                <div class="absolute inset-0 border-2 border-green-500"></div>
                                            @endif
                                        </th>
                                    @endforeach

                                    <!-- Encabezados estadísticas -->
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">Asistencias</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">%</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">Inasistencias</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">%</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($alumnos as $index => $alumno)
                                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                        <td class="px-1 py-2 whitespace-nowrap text-sm text-center border border-gray-300 sticky left-0 z-10 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border border-gray-300 sticky left-8 z-10 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                            {{ $alumno->nombre }}
                                        </td>
                                        <td colspan="2" class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 border border-gray-300 sticky left-40 z-10 {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                            {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}
                                        </td>

                                        <!-- Celdas de asistencia por cada día -->
                                        @foreach($diasDelMes as $dia)
                                            <td
                                                class="p-0 text-center border border-gray-300 {{ in_array($dia['fecha'], $diasNoLaborables) ? 'bg-gray-300' : '' }}"
                                                style="min-width: 24px; height: 24px;"
                                            >
                                                @if(!in_array($dia['fecha'], $diasNoLaborables))
                                                    <div class="w-full h-full flex justify-center items-center">
                                                        @php
                                                            $estado = $asistencias[$alumno->id][$dia['fecha']] ?? 'falta';
                                                            $bgColor = $estado === 'asistio' ? 'bg-green-100' : ($estado === 'falta' ? 'bg-red-100' : 'bg-yellow-100');
                                                            $icon = $estado === 'asistio' ? '✓' : ($estado === 'falta' ? '✗' : '!');
                                                            $textColor = $estado === 'asistio' ? 'text-green-600' : ($estado === 'falta' ? 'text-red-600' : 'text-yellow-600');
                                                        @endphp

                                                        <button
                                                            wire:click="guardarAsistencia({{ $alumno->id }}, '{{ $dia['fecha'] }}', '{{ $estado === 'asistio' ? 'falta' : ($estado === 'falta' ? 'justificada' : 'asistio') }}')"
                                                            class="w-full h-full {{ $bgColor }} font-bold text-lg {{ $textColor }}"
                                                        >
                                                            {{ $icon }}
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Celdas de estadísticas -->
                                        @if(isset($estadisticas[$alumno->id]))
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium border border-gray-300">
                                                {{ $estadisticas[$alumno->id]['asistencias'] }}
                                            </td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium border border-gray-300">
                                                {{ $estadisticas[$alumno->id]['porcentaje_asistencia'] }}%
                                            </td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium border border-gray-300">
                                                {{ $estadisticas[$alumno->id]['inasistencias'] }}
                                            </td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium border border-gray-300">
                                                {{ $estadisticas[$alumno->id]['porcentaje_inasistencia'] }}%
                                            </td>
                                        @else
                                            <td colspan="4" class="px-2 py-2 whitespace-nowrap text-sm text-center border border-gray-300">
                                                Sin datos
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Leyenda y ayuda -->
                    <div class="mt-4 flex flex-wrap items-center space-x-6">
                        <div class="flex items-center space-x-2 mt-2">
                            <div class="w-4 h-4 bg-green-100 flex items-center justify-center font-bold text-green-600">✓</div>
                            <span class="text-sm">Asistencia</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-2">
                            <div class="w-4 h-4 bg-red-100 flex items-center justify-center font-bold text-red-600">✗</div>
                            <span class="text-sm">Falta</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-2">
                            <div class="w-4 h-4 bg-yellow-100 flex items-center justify-center font-bold text-yellow-600">!</div>
                            <span class="text-sm">Justificada</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-2">
                            <div class="w-4 h-4 bg-gray-300"></div>
                            <span class="text-sm">Día no laborable</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-sm text-gray-500">Haga clic en una celda para cambiar el estado de asistencia (Asistencia → Falta → Justificada → Asistencia)</span>
                        </div>
                    </div>

                    <!-- Información sobre estadísticas por campo formativo -->
                    @if(!empty($camposFormativosPorDia) && !$editandoCamposFormativos && !$editandoNoLaborables && count($estadisticasPorCampoFormativo) > 0)
                        <div class="mt-6">
                            <h3 class="text-lg font-bold mb-3">Estadísticas por Campo Formativo</h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                    <thead>
                                        <tr class="bg-blue-200">
                                            <th class="px-1 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">No.</th>
                                            <th class="px-6 py-2 text-left text-xs font-medium text-gray-800 border border-gray-300">Nombre</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">Apellidos</th>
                                            @foreach($camposFormativos as $campo)
                                                <th colspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">
                                                    <div class="flex flex-col items-center">
                                                        <span>{{ $campo->nombre }}</span>
                                                        <div class="w-4 h-2 mt-1 {{ $coloresCamposFormativos[$campo->id] ?? 'bg-gray-300' }}"></div>
                                                    </div>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($alumnos as $index => $alumno)
                                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                                <td class="px-1 py-2 whitespace-nowrap text-sm text-center border border-gray-300">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border border-gray-300">
                                                    {{ $alumno->nombre }}
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 border border-gray-300">
                                                    {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}
                                                </td>

                                                @foreach($camposFormativos as $campo)
                                                    @if(isset($estadisticasPorCampoFormativo[$alumno->id][$campo->id]))
                                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium border border-gray-300">
                                                            {{ $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['asistencias'] }}/{{ $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['total_dias'] }}
                                                        </td>
                                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-center font-medium border border-gray-300 {{ $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['porcentaje_asistencia'] < 80 ? 'text-red-600' : 'text-green-600' }}">
                                                            {{ $estadisticasPorCampoFormativo[$alumno->id][$campo->id]['porcentaje_asistencia'] }}%
                                                        </td>
                                                    @else
                                                        <td colspan="2" class="px-2 py-2 whitespace-nowrap text-sm text-center border border-gray-300">
                                                            -
                                                        </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Modal para selección de campos formativos -->
                @if($diaSeleccionadoParaCampos)
                    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                            <h3 class="text-lg font-bold mb-4">
                                Seleccionar Campos Formativos para
                                {{ date('l', strtotime($diaSeleccionadoParaCampos)) == 'Monday' ? 'Lunes' :
                                  (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Tuesday' ? 'Martes' :
                                  (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Wednesday' ? 'Miércoles' :
                                  (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Thursday' ? 'Jueves' :
                                  (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Friday' ? 'Viernes' :
                                  (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Saturday' ? 'Sábado' : 'Domingo'))))) }}
                                {{ date('d/m/Y', strtotime($diaSeleccionadoParaCampos)) }}
                            </h3>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Seleccione los campos formativos que se trabajarán este día:</p>

                                <div class="space-y-2 max-h-60 overflow-y-auto mb-4">
                                    @foreach($camposFormativos as $campo)
                                        <label class="flex items-center space-x-2">
                                            <input
                                                type="checkbox"
                                                wire:click="toggleCampoFormativo({{ $campo->id }})"
                                                {{ in_array($campo->id, $camposSeleccionados) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            >
                                            <span class="text-gray-700">{{ $campo->nombre }}</span>
                                            <div class="w-4 h-4 {{ $coloresCamposFormativos[$campo->id] ?? 'bg-gray-300' }}"></div>
                                        </label>
                                    @endforeach
                                </div>

                                <!-- Opciones adicionales -->
                                <div class="space-y-3 border-t pt-3 mb-4">
                                    <p class="font-medium text-gray-700">Opciones adicionales:</p>

                                    <!-- Repetir para todos los días iguales del mes -->
                                    <div>
                                        <button
                                            wire:click="aplicarATodosDiasSimilares('{{ $diaSeleccionadoParaCampos }}')"
                                            wire:confirm="¿Está seguro que desea aplicar esta configuración a todos los {{ date('l', strtotime($diaSeleccionadoParaCampos)) == 'Monday' ? 'lunes' : (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Tuesday' ? 'martes' : (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Wednesday' ? 'miércoles' : (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Thursday' ? 'jueves' : (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Friday' ? 'viernes' : (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Saturday' ? 'sábados' : 'domingos'))))) }} del mes?"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm flex items-center"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Aplicar a todos los
                                            {{ date('l', strtotime($diaSeleccionadoParaCampos)) == 'Monday' ? 'lunes' :
                                              (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Tuesday' ? 'martes' :
                                              (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Wednesday' ? 'miércoles' :
                                              (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Thursday' ? 'jueves' :
                                              (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Friday' ? 'viernes' :
                                              (date('l', strtotime($diaSeleccionadoParaCampos)) == 'Saturday' ? 'sábados' : 'domingos'))))) }}
                                            del mes
                                        </button>
                                    </div>

                                    <!-- Aplicar a un rango de fechas -->
                                    <div>
                                        <button
                                            wire:click="mostrarModalRangoFechas"
                                            class="text-blue-600 hover:text-blue-900 text-sm flex items-center"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Aplicar a todos los días similares en un rango de fechas
                                        </button>
                                    </div>

                                    <!-- Borrar toda la planificación del mes -->
                                    <div>
                                        <button
                                            wire:click="confirmarBorrarPlanificacion"
                                            class="text-red-600 hover:text-red-900 text-sm flex items-center"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Borrar toda la planificación del mes
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button
                                    wire:click="seleccionarDiaParaCampos('{{ $diaSeleccionadoParaCampos }}')"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    wire:click="guardarCamposFormativos"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                >
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Modal para seleccionar rango de fechas -->
                @if($mostrandoModalRangoFechas)
                    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                            <h3 class="text-lg font-bold mb-4">Aplicar a días específicos en un rango de fechas</h3>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-4">Seleccione el día de la semana y el rango de fechas para aplicar los campos formativos seleccionados:</p>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Día de la semana:</label>
                                    <select wire:model="selectedDayOfWeek" class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Seleccione un día</option>
                                        <option value="1">Lunes</option>
                                        <option value="2">Martes</option>
                                        <option value="3">Miércoles</option>
                                        <option value="4">Jueves</option>
                                        <option value="5">Viernes</option>
                                        <option value="6">Sábado</option>
                                        <option value="0">Domingo</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio:</label>
                                        <input
                                            type="date"
                                            wire:model="fechaInicioRango"
                                            class="w-full border-gray-300 rounded-md shadow-sm"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin:</label>
                                        <input
                                            type="date"
                                            wire:model="fechaFinRango"
                                            class="w-full border-gray-300 rounded-md shadow-sm"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button
                                    wire:click="cerrarModalRangoFechas"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    wire:click="aplicarARangoFechas"
                                    wire:confirm="¿Está seguro que desea aplicar esta configuración a los días seleccionados en el rango de fechas?"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                >
                                    Aplicar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Modal de confirmación para borrar toda la planificación -->
                @if($confirmandoBorrado)
                    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                            <div class="text-center">
                                <svg class="mx-auto mb-4 w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">¿Está seguro?</h3>
                                <p class="text-gray-600 mb-6">Esta acción eliminará TODA la planificación de campos formativos para el mes de {{ $this->nombreMes }}. Esta acción no se puede deshacer.</p>

                                <div class="flex justify-center space-x-4">
                                    <button
                                        wire:click="cerrarModalConfirmacionBorrado"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                                    >
                                        No, cancelar
                                    </button>
                                    <button
                                        wire:click="confirmarBorradoFinal"
                                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        Sí, estoy seguro
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Modal de confirmación final para borrar toda la planificación -->
                @if($confirmandoBorradoFinal)
                    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                            <div class="text-center">
                                <svg class="mx-auto mb-4 w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">CONFIRMACIÓN FINAL</h3>
                                <p class="text-gray-600 mb-2">Esta es la confirmación final. Al confirmar, se eliminará TODA la planificación de campos formativos.</p>
                                <p class="font-bold text-red-600 mb-6">¿Realmente desea proceder?</p>

                                <div class="flex justify-center space-x-4">
                                    <button
                                        wire:click="cerrarModalConfirmacionBorradoFinal"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
                                    >
                                        No, cancelar
                                    </button>
                                    <button
                                        wire:click="borrarTodaPlanificacion"
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 flex items-center justify-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Sí, eliminar todo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
