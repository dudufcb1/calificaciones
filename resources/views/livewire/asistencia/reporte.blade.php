<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                            Reportes de Asistencia
                        </h1>
                        <a href="{{ route('asistencia.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700">
                            Volver
                        </a>
                    </div>

                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Genera reportes de asistencia por alumno, grupo o período y visualiza las estadísticas.
                    </p>
                </div>

                <div class="p-6">
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tipoReporte" class="block text-sm font-medium text-gray-700">Tipo de reporte</label>
                            <select wire:model.live="tipoReporte" id="tipoReporte" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="mes">Por mes</option>
                                <option value="personalizado">Periodo personalizado</option>
                            </select>
                        </div>

                        @if($tipoReporte === 'mes')
                        <div>
                            <label for="mes" class="block text-sm font-medium text-gray-700">Mes</label>
                            <select wire:model.live="mes" id="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label for="anio" class="block text-sm font-medium text-gray-700">Año</label>
                            <select wire:model.live="anio" id="anio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @for($i = Carbon\Carbon::now()->year - 5; $i <= Carbon\Carbon::now()->year + 1; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        @else
                        <div>
                            <label for="fechaInicio" class="block text-sm font-medium text-gray-700">Fecha inicio</label>
                            <input wire:model.live="fechaInicio" type="date" id="fechaInicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="fechaFin" class="block text-sm font-medium text-gray-700">Fecha fin</label>
                            <input wire:model.live="fechaFin" type="date" id="fechaFin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        @endif
                    </div>

                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="grupo_id" class="block text-sm font-medium text-gray-700">Grupo</label>
                            <select wire:model.live="grupo_id" id="grupo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Todos los grupos</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="alumno_id" class="block text-sm font-medium text-gray-700">Alumno específico</label>
                            <select wire:model.live="alumno_id" id="alumno_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Todos los alumnos</option>
                                @foreach($alumnos as $alumno)
                                    <option value="{{ $alumno->id }}">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Buscar alumno</label>
                            <input wire:model.live.debounce.300ms="search" type="text" id="search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Nombre o apellidos...">
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <h2 class="text-lg font-medium text-gray-900">Resumen de asistencia</h2>
                                <span class="text-sm text-gray-500">{{ $periodoTexto }}</span>
                            </div>
                        </div>
                    </div>

                    @if(count($reporteData) > 0)
                        <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total días</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asistencias</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faltas</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Justificadas</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Asistencia</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reporteData as $data)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $data['alumno']->apellido_paterno }} {{ $data['alumno']->apellido_materno }} {{ $data['alumno']->nombre }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $data['alumno']->grupo ? $data['alumno']->grupo->nombre : 'Sin grupo' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $data['estadisticas']['total_dias'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $data['estadisticas']['total_asistencias'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $data['estadisticas']['total_faltas'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $data['estadisticas']['total_justificadas'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="relative w-full h-2 bg-gray-200 rounded">
                                                        <div class="absolute top-0 left-0 h-2 {{ $data['estadisticas']['porcentaje_asistencia'] >= 90 ? 'bg-green-500' : ($data['estadisticas']['porcentaje_asistencia'] >= 80 ? 'bg-yellow-500' : 'bg-red-500') }} rounded" style="width: {{ $data['estadisticas']['porcentaje_asistencia'] }}%"></div>
                                                    </div>
                                                    <span class="ml-2 text-sm font-medium {{ $data['estadisticas']['porcentaje_asistencia'] >= 90 ? 'text-green-600' : ($data['estadisticas']['porcentaje_asistencia'] >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                                        {{ $data['estadisticas']['porcentaje_asistencia'] }}%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-md font-medium text-gray-700 mb-2">Leyenda</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="flex items-center">
                                        <span class="w-4 h-4 bg-green-500 rounded mr-2"></span>
                                        <span class="text-sm text-gray-600">90% o más - Excelente</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-4 h-4 bg-yellow-500 rounded mr-2"></span>
                                        <span class="text-sm text-gray-600">80% a 89% - Aceptable</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-4 h-4 bg-red-500 rounded mr-2"></span>
                                        <span class="text-sm text-gray-600">Menos de 80% - Riesgo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-6 text-center">
                            <p class="text-gray-500">No hay datos de asistencia disponibles con los filtros seleccionados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
