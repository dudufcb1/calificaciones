<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Evaluaciones</h1>
            <p class="mt-2 text-sm text-gray-700">Lista de evaluaciones realizadas por momento y campo formativo.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none flex space-x-2">
            <button wire:click="toggleSeleccionMultiple"
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                {{ $seleccionMultiple ? 'Cancelar selección' : 'Selección múltiple' }}
            </button>

            @if($seleccionMultiple && !empty($evaluacionesSeleccionadas))
            <button wire:click="exportarSimple" class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:w-auto">
                Exportar ({{ count($evaluacionesSeleccionadas) }})
            </button>
            <script>
                console.log('Evaluaciones seleccionadas disponibles:', @js($evaluacionesSeleccionadas));
            </script>
            @endif

            <a href="{{ route('evaluaciones.create') }}"
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                Evaluar Momento
            </a>
        </div>
    </div>

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <div class="bg-white px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center space-x-4">
                            <div>
                                <select wire:model.live="momentoFilter"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Todos los momentos</option>
                                    @foreach($momentos as $momento)
                                        <option value="{{ $momento->id }}">{{ $momento->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <label for="search" class="sr-only">Buscar</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="text"
                                           wire:model.live.debounce.300ms="search"
                                           class="block w-full pr-10 sm:text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Buscar por alumno...">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <select wire:model.live="campoFormativoFilter"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Todos los campos formativos</option>
                                    @foreach($camposFormativos as $campo)
                                        <option value="{{ $campo->id }}">{{ $campo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @if($seleccionMultiple)
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Seleccionar
                                </th>
                                @endif
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grupo
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Campo Formativo
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Momento
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Alumnos
                                </th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="relative px-3 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($evaluaciones as $evaluacion)
                                <tr>
                                    @if($seleccionMultiple)
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleSeleccionEvaluacion({{ $evaluacion->id }})"
                                            {{ in_array($evaluacion->id, $evaluacionesSeleccionadas) ? 'checked' : '' }}
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        >
                                    </td>
                                    @endif
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ $evaluacion->grupo ? $evaluacion->grupo->nombre : 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $evaluacion->campoFormativo->nombre }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-gray-900">
                                        {{ $evaluacion->fecha_evaluacion ? $evaluacion->fecha_evaluacion->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $evaluacion->momentoObj ? $evaluacion->momentoObj->nombre : ($evaluacion->momento ? $evaluacion->momento->value : 'No definido') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $evaluacion->detalles->count() }} alumnos
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center text-gray-900">
                                        @php
                                            $totalAlumnos = $evaluacion->detalles->count();
                                            $alumnosCalificados = $evaluacion->detalles->filter(function($detalle) {
                                                return $detalle->promedio_final > 0;
                                            })->count();
                                            $completado = $totalAlumnos > 0 && $alumnosCalificados == $totalAlumnos;
                                        @endphp

                                        @if($completado)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Finalizado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pendiente ({{ $alumnosCalificados }}/{{ $totalAlumnos }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('evaluaciones.show', $evaluacion->id) }}" class="text-blue-600 hover:text-blue-900">
                                                Ver
                                            </a>
                                            <a href="{{ route('evaluaciones.edit', $evaluacion->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                Editar
                                            </a>
                                            <button wire:click="confirmDelete({{ $evaluacion->id }})" class="text-red-600 hover:text-red-900">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $seleccionMultiple ? 8 : 7 }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No hay evaluaciones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $evaluaciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    @if($showDeleteModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Eliminar Evaluación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Está seguro de que desea eliminar esta evaluación? Todos los datos relacionados con esta evaluación se perderán permanentemente. Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteEvaluacion" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Eliminar
                        </button>
                        <button type="button" wire:click="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de confirmación para exportar múltiples evaluaciones -->
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
        x-data="{ open: false }"
        x-init="$watch('open', value => { console.log('Modal Export estado:', value) })"
        x-on:show-export-modal.window="open = true; console.log('Evento Alpine: show-export-modal recibido');"
        x-on:hide-export-modal.window="open = false; console.log('Evento Alpine: hide-export-modal recibido');"
        :class="{'hidden': !open}"
        @if($showExportModal) x-init="() => { open = true; console.log('Modal inicializado como abierto desde servidor'); }" @endif
        x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" x-show="open">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Exportar Diferentes Campos Formativos
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Estás a punto de exportar {{ count($evaluacionesSeleccionadas) }} evaluaciones en un solo archivo Excel. Cada evaluación tendrá su propia hoja en el archivo.
                                </p>
                                <div class="mt-2 py-1 px-2 bg-indigo-100 text-indigo-800 text-sm rounded">
                                    Estado del modal: <span x-text="open ? 'Abierto' : 'Cerrado'"></span>
                                    <br>
                                    Evaluaciones seleccionadas: {{ count($evaluacionesSeleccionadas) }}
                                </div>

                                @if(auth()->user()->trial)
                                <p class="mt-2 text-sm text-red-500 font-semibold">
                                    Esta función solo está disponible para usuarios con membresía premium.
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="exportarMultiplesEvaluaciones" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Exportar
                    </button>
                    <button wire:click="cancelExport" @click="open = false; console.log('Botón Cancelar clickeado manualmente')" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
