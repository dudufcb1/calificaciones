<div class="py-6">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Cabecera con selección de grupo y mes -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">
                    <div class="flex items-center space-x-4">
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

                        <!-- Botón para editar días no laborables -->
                        <button wire:click="toggleEdicionNoLaborables" class="px-3 py-2 rounded text-white {{ $editandoNoLaborables ? 'bg-red-500 hover:bg-red-600' : 'bg-indigo-500 hover:bg-indigo-600' }}">
                            {{ $editandoNoLaborables ? 'Terminar edición' : 'Editar días no laborables' }}
                        </button>
                    </div>
                </div>

                @if($editandoNoLaborables)
                    <div class="mb-4 p-3 bg-yellow-100 rounded">
                        <p class="text-sm text-yellow-800">
                            <strong>Modo edición:</strong> Haga clic en las fechas de la cabecera para marcar/desmarcar días no laborables.
                            Los días no laborables se mostrarán en gris y no se contarán para el cálculo de porcentajes.
                        </p>
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
                                    <th colspan="4" class="px-6 py-2 bg-yellow-300 text-center font-bold text-lg border-b border-gray-300">
                                        Ciclo escolar 2023-2024
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
                                    <th class="px-1 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">No.</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-800 border border-gray-300">Nombre</th>
                                    <th colspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-800 border border-gray-300">Apellidos</th>

                                    <!-- Días del mes -->
                                    @foreach($diasDelMes as $dia)
                                        <th
                                            wire:click="toggleDiaNoLaborable('{{ $dia['fecha'] }}')"
                                            class="px-1 py-1 text-center text-xs font-medium {{ in_array($dia['fecha'], $diasNoLaborables) ? 'bg-gray-300' : ($dia['es_fin_semana'] ? 'bg-gray-100' : 'bg-blue-200') }} cursor-pointer border border-gray-300 {{ $editandoNoLaborables ? 'hover:bg-yellow-200' : '' }}"
                                        >
                                            {{ $dia['numero'] }}
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
                                        <td class="px-1 py-2 whitespace-nowrap text-sm text-center border border-gray-300">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border border-gray-300">
                                            {{ $alumno->nombre }}
                                        </td>
                                        <td colspan="2" class="px-2 py-2 whitespace-nowrap text-sm text-gray-500 border border-gray-300">
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
                                                            $bgColor = $estado === 'asistio' ? 'bg-green-500' : ($estado === 'falta' ? 'bg-red-500' : 'bg-yellow-500');
                                                        @endphp

                                                        <button
                                                            wire:click="guardarAsistencia({{ $alumno->id }}, '{{ $dia['fecha'] }}', '{{ $estado === 'asistio' ? 'falta' : ($estado === 'falta' ? 'justificada' : 'asistio') }}')"
                                                            class="w-full h-full {{ $bgColor }}"
                                                        ></button>
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
                            <div class="w-4 h-4 bg-green-500"></div>
                            <span class="text-sm">Asistencia</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-2">
                            <div class="w-4 h-4 bg-red-500"></div>
                            <span class="text-sm">Falta</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-2">
                            <div class="w-4 h-4 bg-yellow-500"></div>
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
                @endif
            </div>
        </div>
    </div>
</div>
