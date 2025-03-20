<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">
                        Detalle de Evaluación
                    </h2>
                    <div>
                        @if(!$evaluacion->is_draft)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Finalizada
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Borrador
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Información general de la evaluación -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 bg-gray-50 p-4 rounded-md">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Título</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $evaluacion->titulo }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Campo Formativo</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $evaluacion->campoFormativo->nombre }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Fecha</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'No definida' }}</p>
                        </div>

                        <div class="col-span-2">
                            <p class="text-sm font-medium text-gray-500">Descripción</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $evaluacion->descripcion ?: 'Sin descripción' }}</p>
                        </div>
                    </div>

                    <!-- Criterios de Evaluación -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Criterios de Evaluación</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Criterio
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Descripción
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Porcentaje
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($criterios as $criterio)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $criterio['nombre'] }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $criterio['descripcion'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $criterio['porcentaje'] }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Alumnos Evaluados -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Alumnos Evaluados</h3>

                        @if(count($detalles) > 0)
                            <div class="mb-4 flex justify-end">
                                <button type="button" wire:click="actualizarPromediosEnBD"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Recalcular y guardar promedios
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Alumno
                                            </th>
                                            @foreach($criterios as $criterio)
                                                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ $criterio['nombre'] }}
                                                </th>
                                            @endforeach
                                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Promedio
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($detalles as $detalle)
                                            <tr>
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $detalle['nombre'] }}
                                                </td>
                                                @foreach($detalle['calificaciones'] as $calificacion)
                                                    <td class="px-2 py-4 whitespace-nowrap text-sm">
                                                        <span class="text-gray-900 font-medium">{{ $calificacion['valor'] }}</span>
                                                        <span class="text-gray-500 text-xs block">({{ number_format($calificacion['ponderada'], 2) }})</span>
                                                    </td>
                                                @endforeach
                                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                    {{ number_format($detalle['promedio'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="py-4 text-center text-gray-500">
                                No hay alumnos evaluados.
                            </div>
                        @endif
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex justify-end space-x-3">
                        <button wire:click="exportarExcel" type="button"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Exportar a Excel
                        </button>
                        <a href="{{ route('evaluaciones.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Volver
                        </a>
                        <a href="{{ route('evaluaciones.edit', $evaluacion->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
