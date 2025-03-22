<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Gestión de Momentos Educativos
                    </h2>
                    <div class="flex space-x-4">
                        <div class="flex rounded-md shadow-sm">
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar momentos..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block w-full">
                        </div>
                        <select wire:model.live="ciclo_filter" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todos los ciclos</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}">{{ $ciclo->nombre_formateado }}</option>
                            @endforeach
                        </select>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nuevo Momento
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciclo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rango de Fechas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campos Formativos</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($momentos as $momento)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $momento->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $momento->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $momento->ciclo->nombre }}
                                        @if($momento->ciclo->activo)
                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Activo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $momento->fecha->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($momento->tieneRangoFechas())
                                            {{ $momento->fecha_inicio->format('d/m/Y') }} - {{ $momento->fecha_fin->format('d/m/Y') }}
                                        @else
                                            <span class="text-gray-400">No definido</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($momento->camposFormativos->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($momento->camposFormativos as $campoFormativo)
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                                        {{ $campoFormativo->nombre }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400">No hay campos formativos</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="edit({{ $momento->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $momento->id }})" class="text-red-600 hover:text-red-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No hay momentos registrados. Crea uno nuevo usando el botón "Nuevo Momento".
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $momentos->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar momentos -->
    <div class="fixed inset-0 flex items-center justify-center z-50" style="display: {{ $isOpen ? 'flex' : 'none' }}">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>

        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-10">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $momento_id ? 'Editar Momento' : 'Nuevo Momento' }}
                        </h3>
                        <div class="mt-2">
                            <form>
                                <div class="mt-4">
                                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                                    <input type="text" id="nombre" wire:model="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="mt-4">
                                    <label for="ciclo_id" class="block text-sm font-medium text-gray-700">Ciclo Escolar</label>
                                    <select id="ciclo_id" wire:model="ciclo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleccione un ciclo...</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre_formateado }}{{ $ciclo->activo ? ' (Activo)' : '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('ciclo_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="mt-4">
                                    <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha del Momento</label>
                                    <input type="date" id="fecha" wire:model="fecha" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('fecha') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="mt-4">
                                    <label for="rangoFechas" class="inline-flex items-center">
                                        <input type="checkbox" id="rangoFechas" wire:model.live="rangoFechas" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">Definir rango de fechas</span>
                                    </label>
                                </div>

                                @if($rangoFechas)
                                    <div class="mt-4 grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                            <input type="date" id="fecha_inicio" wire:model="fecha_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('fecha_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                                            <input type="date" id="fecha_fin" wire:model="fecha_fin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error('fecha_fin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Campos Formativos</label>
                                    <div class="mt-2 border border-gray-300 rounded-md p-2 max-h-40 overflow-y-auto">
                                        @if(count($camposFormativos) > 0)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($camposFormativos as $campoFormativo)
                                                    <div class="flex items-center space-x-2">
                                                        <input
                                                            type="checkbox"
                                                            id="campo_{{ $campoFormativo['id'] }}"
                                                            value="{{ $campoFormativo['id'] }}"
                                                            wire:click="toggleCampoFormativo({{ $campoFormativo['id'] }})"
                                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            {{ in_array($campoFormativo['id'], $selectedCamposFormativos) ? 'checked' : '' }}
                                                        >
                                                        <label for="campo_{{ $campoFormativo['id'] }}" class="text-sm text-gray-700">
                                                            {{ $campoFormativo['nombre'] }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500">No hay campos formativos disponibles</p>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="store" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ $momento_id ? 'Actualizar' : 'Crear' }}
                </button>
                <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="fixed inset-0 flex items-center justify-center z-50" style="display: {{ $isConfirmingDelete ? 'flex' : 'none' }}">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>

        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-10">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Eliminar Momento
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                ¿Estás seguro de que deseas eliminar este momento? Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="delete" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Eliminar
                </button>
                <button wire:click="cancelDelete" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
